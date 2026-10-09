<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Services\OllamaService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithRoles;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_admin_chatbot_returns_ollama_response_with_database_context(): void
    {
        $mock = new class extends OllamaService {
            public function __construct() {}
            public function generateFromConversation(array $messages, ?string $model = null): array
            {
                return ['success' => true, 'text' => 'Mocked AI response'];
            }
        };

        $this->instance(OllamaService::class, $mock);

        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->postJson('/api/chatbot/query', [
            'message' => 'Analyze patterns in the current dataset',
            'pageType' => 'bookings',
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'response' => 'Mocked AI response'
        ]);
    }

    public function test_ai_report_endpoints_use_aggregated_database_results(): void
    {
        $this->instance(OllamaService::class, new class extends OllamaService {
            public function __construct() {}

            public function generateText(string $prompt, ?string $model = null, ?string $systemPrompt = null): array
            {
                return ['success' => true, 'text' => 'Mocked report'];
            }
        });

        $this->actingAs($this->createAdmin());

        $this->getJson(route('admin.ai.insights'))
            ->assertOk()
            ->assertJsonPath('data.total_bookings', 0)
            ->assertJsonPath('data.total_inventory', 0)
            ->assertJsonPath('data.total_borrow_requests', 0);

        $this->getJson(route('admin.ai.forecast'))
            ->assertOk()
            ->assertJsonPath('historical_data', []);

        $this->getJson(route('admin.ai.inventory-recommendations'))
            ->assertOk()
            ->assertJsonPath('inventory_data', []);
    }

    public function test_chatbot_requires_authentication(): void
    {
        $response = $this->postJson('/api/chatbot/query', [
            'message' => 'Give me a summary',
            'pageType' => 'bookings',
        ]);

        $response->assertUnauthorized();
    }

    public function test_chatbot_is_not_available_to_employees(): void
    {
        $this->actingAs($this->createEmployee())
            ->postJson('/api/chatbot/query', ['message' => 'Give me a summary'])
            ->assertForbidden();
    }

    public function test_chatbot_rejects_overlong_messages(): void
    {
        $this->actingAs($this->createAdmin());

        $response = $this->postJson('/api/chatbot/query', [
            'message' => str_repeat('a', 501),
            'pageType' => 'bookings',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('message');
    }

    public function test_gemini_fallback_receives_the_real_report_context(): void
    {
        $this->instance(OllamaService::class, new class extends OllamaService {
            public function __construct() {}
            public function generateFromConversation(array $messages, ?string $model = null): array
            {
                return ['success' => false, 'error' => 'offline'];
            }
        });

        $this->instance(GeminiService::class, new class extends GeminiService {
            public array $captured = [];
            public function __construct() {}
            public function generateText(string $prompt, string $model = 'gemini-2.0-flash', ?string $systemPrompt = null): array
            {
                $this->captured = [$prompt, $model, $systemPrompt];
                return ['success' => true, 'text' => 'Context-grounded response'];
            }
        });

        $response = $this->actingAs($this->createAdmin())
            ->postJson('/api/chatbot/query', [
                'message' => 'Explain the current report trends',
                'pageType' => 'bookings',
            ]);

        $response->assertOk()->assertJsonPath('response', 'Context-grounded response');
        $this->assertStringContainsString('Total Bookings: 0', $this->app[GeminiService::class]->captured[2]);
    }

    public function test_rule_based_fallback_uses_live_database_aggregates(): void
    {
        $this->instance(OllamaService::class, new class extends OllamaService {
            public function __construct() {}
            public function generateFromConversation(array $messages, ?string $model = null): array
            {
                return ['success' => false, 'error' => 'offline'];
            }
        });
        $this->instance(GeminiService::class, new class extends GeminiService {
            public function __construct() {}
            public function generateText(string $prompt, string $model = 'gemini-2.0-flash', ?string $systemPrompt = null): array
            {
                return ['success' => false, 'error' => 'offline'];
            }
        });

        $response = $this->actingAs($this->createAdmin())
            ->postJson('/api/chatbot/query', ['message' => 'Explain the current report trends']);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Total Bookings: 0') && str_contains($text, 'Total Users: 1'));
    }
}
