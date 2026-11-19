<?php

namespace Tests\Feature\Api\V1;

use App\Models\ChatbotInteraction;
use App\Models\InventoryCategory;
use App\Services\OllamaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

/**
 * Requirement T1-T4: the AI integration runs through the REST API, records
 * every use for traceability, and degrades gracefully when the model is down.
 */
class V1AiInsightTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();
    }

    public function test_ai_endpoints_require_authentication(): void
    {
        foreach (['insights', 'forecast', 'inventory-recommendations'] as $endpoint) {
            $this->getJson("/api/v1/ai/{$endpoint}")
                ->assertStatus(401)
                ->assertJsonPath('error.code', 'UNAUTHENTICATED');
        }
    }

    public function test_employee_cannot_reach_admin_ai_reports(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);

        $this->getJson('/api/v1/ai/insights')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_insights_return_data_and_a_model_answer(): void
    {
        $this->fakeOllama('Bookings look healthy overall.');

        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->getJson('/api/v1/ai/insights')
            ->assertOk()
            ->assertJsonPath('data.report', 'insights')
            ->assertJsonPath('data.source', 'model')
            ->assertJsonPath('data.generated_text', 'Bookings look healthy overall.')
            ->assertJsonStructure([
                'data' => [
                    'report', 'generated_text', 'source', 'confidence',
                    'confidence_note', 'model', 'response_time_ms', 'disclosure', 'inputs',
                ],
            ]);
    }

    public function test_each_use_is_recorded_for_traceability(): void
    {
        $this->fakeOllama('Advisory text.');

        $admin = $this->createAdmin();
        Sanctum::actingAs($admin, ['access']);

        $this->getJson('/api/v1/ai/insights')->assertOk();

        $this->assertDatabaseHas('chatbot_interactions', [
            'user_id' => $admin->id,
            'page_type' => 'insights',
            'outcome' => 'success',
        ]);

        $record = ChatbotInteraction::firstOrFail();
        $this->assertNotNull($record->question_hash);
        $this->assertSame(64, strlen($record->question_hash));
        $this->assertNotNull($record->confidence);
        $this->assertNotNull($record->created_at);
    }

    public function test_falls_back_when_the_model_is_unavailable(): void
    {
        $this->fakeOllamaFailure();

        Sanctum::actingAs($this->createAdmin(), ['access']);

        $response = $this->getJson('/api/v1/ai/insights')
            ->assertOk()
            ->assertJsonPath('data.source', 'deterministic-fallback')
            ->assertJsonPath('data.confidence', 0);

        // A clear, useful message rather than an error (T3).
        $this->assertNotEmpty($response->json('data.generated_text'));
        $this->assertDatabaseHas('chatbot_interactions', ['outcome' => 'fallback']);
    }

    public function test_inventory_recommendations_highlight_low_stock_items(): void
    {
        InventoryCategory::firstOrCreate(['slug' => 'tools'], ['name' => 'Tools', 'is_active' => true]);
        $this->createInventory(['name' => 'Safety Helmet', 'quantity' => 0, 'low_stock_threshold' => 5]);

        $this->fakeOllamaFailure();
        Sanctum::actingAs($this->createAdmin(), ['access']);

        $response = $this->getJson('/api/v1/ai/inventory-recommendations')->assertOk();

        $this->assertStringContainsString('Safety Helmet', $response->json('data.generated_text'));
        $this->assertSame('inventory-recommendations', $response->json('data.report'));
    }

    public function test_forecast_handles_an_empty_history(): void
    {
        $this->fakeOllamaFailure();
        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->getJson('/api/v1/ai/forecast')
            ->assertOk()
            ->assertJsonPath('data.report', 'forecast')
            ->assertJsonStructure(['data' => ['generated_text', 'inputs' => ['monthly_bookings']]]);
    }

    private function fakeOllama(string $text): void
    {
        $this->mock(OllamaService::class, function ($mock) use ($text) {
            $mock->shouldReceive('generateText')
                ->andReturn(['success' => true, 'text' => $text, 'body' => null]);
        });
    }

    private function fakeOllamaFailure(): void
    {
        $this->mock(OllamaService::class, function ($mock) {
            $mock->shouldReceive('generateText')
                ->andReturn(['success' => false, 'error' => 'Ollama service unavailable.']);
        });
    }
}
