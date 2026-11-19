<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Services\OllamaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class ChatbotConversationTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    private function offlineModel(): OllamaService
    {
        return new class extends OllamaService
        {
            public array $calls = [];

            public function __construct() {}

            public function generateFromConversation(array $messages, ?string $model = null): array
            {
                $this->calls[] = $messages;

                return ['success' => true, 'text' => 'The language model must not answer record questions.'];
            }
        };
    }

    public function test_how_about_follow_up_finds_the_user_and_a_similar_name_booking(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Rod Andro Cutillar', 'email' => 'rod@example.com']);

        Booking::factory()->create([
            'reference_number' => 'BK-ROD-001',
            'employee_id' => null,
            'full_name' => 'Rod Andro Carreon Cutillar',
            'email' => 'other@example.com',
            'preferred_date' => Carbon::create(now()->year, 6, 1),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'how about rod']);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'User found: Rod Andro Cutillar')
                && str_contains($text, 'Bookings under a similar name')
                && str_contains($text, 'BK-ROD-001')
                && str_contains($text, 'Rod Andro Carreon Cutillar')
                && ! str_contains($text, 'I found 2 matching records'));
    }

    public function test_a_bare_name_looks_the_person_up(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Maria Santos', 'email' => 'maria@example.com']);

        $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'maria'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'User found: Maria Santos'));
    }

    public function test_list_all_users_returns_user_rows(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Maria Santos', 'email' => 'maria@example.com']);

        $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'list all users'])
            ->assertOk()
            ->assertJsonPath('sources.0', 'db:users')
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Users (2 found)')
                && str_contains($text, 'System Admin')
                && str_contains($text, 'Maria Santos')
                && str_contains($text, 'Admins: 1')
                && str_contains($text, 'Employees: 1'));
    }

    public function test_list_employees_excludes_administrators(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Maria Santos', 'email' => 'maria@example.com']);

        $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'list employees'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Employees (1 found)')
                && str_contains($text, 'Maria Santos')
                && ! str_contains($text, 'System Admin'));
    }

    public function test_show_pending_bookings_lists_only_pending_rows(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $year = now()->year;

        Booking::factory()->create([
            'reference_number' => 'BK-PEND-001',
            'full_name' => 'Pending Person',
            'preferred_date' => Carbon::create($year, 7, 1),
            'status' => 'pending',
        ]);
        Booking::factory()->create([
            'reference_number' => 'BK-APP-001',
            'full_name' => 'Approved Person',
            'preferred_date' => Carbon::create($year, 7, 2),
            'status' => 'approved',
        ]);

        $this->postJson('/api/chatbot/query', ['message' => 'show pending bookings'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Bookings — status: pending')
                && str_contains($text, 'BK-PEND-001')
                && str_contains($text, 'Pending Person')
                && ! str_contains($text, 'BK-APP-001'));
    }

    public function test_show_low_stock_items_lists_only_low_stock_rows(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $this->createInventory(['name' => 'Low Item', 'quantity' => 1, 'low_stock_threshold' => 5]);
        $this->createInventory(['name' => 'High Item', 'quantity' => 50, 'low_stock_threshold' => 5]);

        $this->postJson('/api/chatbot/query', ['message' => 'show low stock items'])
            ->assertOk()
            ->assertJsonPath('sources.0', 'db:inventories')
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Low stock items')
                && str_contains($text, 'Low Item')
                && str_contains($text, 'LOW STOCK')
                && ! str_contains($text, 'High Item'));
    }

    public function test_show_borrow_requests_lists_request_rows(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $employee = $this->createEmployee(['name' => 'Maria Santos']);

        BorrowRequest::factory()->create([
            'request_number' => 'BR-LIST-001',
            'employee_id' => $employee->id,
            'status' => 'pending',
            'borrow_date' => Carbon::create(now()->year, 7, 1),
            'due_date' => Carbon::create(now()->year, 7, 8),
        ]);

        $this->postJson('/api/chatbot/query', ['message' => 'show borrow requests'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Borrow requests (1 found)')
                && str_contains($text, 'BR-LIST-001')
                && str_contains($text, 'Maria Santos'));
    }

    public function test_booking_reference_returns_the_booking_detail(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));

        Booking::factory()->create([
            'reference_number' => 'BK-REF-001',
            'full_name' => 'Reference Person',
            'preferred_date' => Carbon::create(now()->year, 8, 1),
            'status' => 'approved',
        ]);

        $this->postJson('/api/chatbot/query', ['message' => 'show booking BK-REF-001'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Booking BK-REF-001')
                && str_contains($text, 'Reference Person')
                && str_contains($text, 'Status: approved'));
    }

    public function test_inventory_code_returns_the_item_detail(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $this->createInventory([
            'item_code' => 'RGV-777',
            'name' => 'Test Drill',
            'quantity' => 3,
            'low_stock_threshold' => 1,
        ]);

        $this->postJson('/api/chatbot/query', ['message' => 'show item RGV-777'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Inventory RGV-777 — Test Drill')
                && str_contains($text, 'Quantity: 3')
                && str_contains($text, 'Stock flag: OK'));
    }

    public function test_show_overdue_borrow_requests_reports_the_late_rows(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $employee = $this->createEmployee(['name' => 'Maria Santos']);

        BorrowRequest::factory()->create([
            'request_number' => 'BR-LATE-001',
            'employee_id' => $employee->id,
            'status' => 'approved',
            'borrow_date' => now()->subDays(10),
            'due_date' => now()->subDays(3),
        ]);

        $this->postJson('/api/chatbot/query', ['message' => 'show overdue borrow requests'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Overdue borrow requests: 1')
                && str_contains($text, 'BR-LATE-001'));
    }
}
