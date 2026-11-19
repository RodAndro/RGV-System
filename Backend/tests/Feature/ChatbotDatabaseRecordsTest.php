<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\ChatbotInteraction;
use App\Services\OllamaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class ChatbotDatabaseRecordsTest extends TestCase
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

    public function test_person_lookup_scans_users_bookings_borrowings_and_returns(): void
    {
        $model = $this->offlineModel();
        $this->instance(OllamaService::class, $model);

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $employee = $this->createEmployee(['name' => 'Rodan Cruz', 'email' => 'rodan@example.com']);
        $year = now()->year;
        Booking::factory()->create([
            'reference_number' => 'BK-TEST-001',
            'employee_id' => $employee->id,
            'full_name' => 'Rodan Cruz',
            'email' => 'rodan@example.com',
            'preferred_date' => Carbon::create($year, 1, 15),
            'status' => 'approved',
        ]);

        $inventory = $this->createInventory(['name' => 'Impact Drill', 'quantity' => 4]);

        $borrow = BorrowRequest::factory()->create([
            'request_number' => 'BR-TEST-001',
            'employee_id' => $employee->id,
            'status' => 'returned',
            'borrow_date' => Carbon::create($year, 1, 20),
            'due_date' => Carbon::create($year, 1, 27),
            'return_date' => Carbon::create($year, 1, 25),
            'returned_at' => Carbon::create($year, 1, 25, 10, 0),
        ]);

        BorrowItem::factory()->create([
            'borrow_request_id' => $borrow->id,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'is_returned' => true,
            'returned_at' => Carbon::create($year, 1, 25, 10, 0),
            'condition_returned' => 'good',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'Is there anyone named Rod in the users?']);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sources.0', 'db:users')
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'User found: Rodan Cruz')
                && str_contains($text, 'Bookings (1 total)')
                && str_contains($text, 'BK-TEST-001')
                && str_contains($text, 'Borrowing history (1 total)')
                && str_contains($text, 'BR-TEST-001')
                && str_contains($text, 'Impact Drill')
                && str_contains($text, 'Returned items (1 recorded)')
                && str_contains($text, 'Pending and completed tasks'));

        $this->assertSame([], $model->calls, 'Record questions must be answered from the database, not the model.');

        $interaction = ChatbotInteraction::query()->latest('id')->first();
        $this->assertSame('records', $interaction->page_type);
        $this->assertSame('deterministic', $interaction->model_name);
        $this->assertSame('answered', $interaction->outcome);
        $this->assertSame(1.0, $interaction->confidence);
    }

    public function test_booking_count_for_a_month_range_lists_each_person_and_status(): void
    {
        $model = $this->offlineModel();
        $this->instance(OllamaService::class, $model);

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $year = now()->year;

        Booking::factory()->create([
            'reference_number' => 'BK-JAN-001',
            'full_name' => 'Ana Reyes',
            'preferred_date' => Carbon::create($year, 1, 10),
            'status' => 'approved',
        ]);
        Booking::factory()->create([
            'reference_number' => 'BK-FEB-001',
            'full_name' => 'Ben Cruz',
            'preferred_date' => Carbon::create($year, 2, 5),
            'status' => 'pending',
        ]);
        Booking::factory()->create([
            'reference_number' => 'BK-MAR-001',
            'full_name' => 'Cara Lim',
            'preferred_date' => Carbon::create($year, 3, 5),
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/chatbot/query', [
            'message' => 'How many people have booked from January to February?',
        ]);

        $response->assertOk()
            ->assertJsonPath('sources.0', 'db:bookings')
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Total bookings: 2')
                && str_contains($text, 'Distinct people who booked: 2')
                && str_contains($text, 'Ana Reyes')
                && str_contains($text, 'Ben Cruz')
                && ! str_contains($text, 'Cara Lim')
                && str_contains($text, 'Approved: 1')
                && str_contains($text, 'Pending: 1'));

        $this->assertSame([], $model->calls);
    }

    public function test_empty_month_range_states_no_records_and_points_to_years_with_data(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin());
        $previousYear = now()->year - 1;

        Booking::factory()->create([
            'reference_number' => 'BK-OLD-001',
            'full_name' => 'Old Record',
            'preferred_date' => Carbon::create($previousYear, 1, 10),
            'status' => 'completed',
        ]);

        $response = $this->postJson('/api/chatbot/query', [
            'message' => 'How many people booked from January to February?',
        ]);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'No booking records were found in that date range.')
                && str_contains($text, 'The year was not specified')
                && str_contains($text, 'Matching records exist for the same months in: '.$previousYear.' (1 booking)'));
    }

    public function test_explicit_day_month_range_is_parsed_exactly(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $year = now()->year;

        Booking::factory()->create([
            'reference_number' => 'BK-JAN-DAY',
            'full_name' => 'Ana Reyes',
            'preferred_date' => Carbon::create($year, 1, 15),
        ]);
        Booking::factory()->create([
            'reference_number' => 'BK-FEB-DAY',
            'full_name' => 'Ben Cruz',
            'preferred_date' => Carbon::create($year, 2, 20),
        ]);
        Booking::factory()->create([
            'reference_number' => 'BK-MAR-DAY',
            'full_name' => 'Cara Lim',
            'preferred_date' => Carbon::create($year, 3, 1),
        ]);

        $response = $this->postJson('/api/chatbot/query', [
            'message' => "Show me bookings from Jan 1, {$year} to Feb 28, {$year}",
        ]);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Total bookings: 2')
                && str_contains($text, 'Ana Reyes')
                && str_contains($text, 'Ben Cruz')
                && ! str_contains($text, 'Cara Lim'));
    }

    public function test_lowercase_who_is_lookup_finds_the_person(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Rodan Cruz', 'email' => 'rodan@example.com']);

        $response = $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'who is rodan?']);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'User found: Rodan Cruz'));
    }

    public function test_booking_lookup_by_full_name_uses_that_person_only(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $target = $this->createEmployee(['name' => 'Rodan Cruz', 'email' => 'rodan@example.com']);
        $other = $this->createEmployee(['name' => 'Juan Cruz', 'email' => 'juan@example.com']);
        $year = now()->year;

        Booking::factory()->create([
            'reference_number' => 'BK-RODAN-001',
            'employee_id' => $target->id,
            'full_name' => 'Rodan Cruz',
            'email' => 'rodan@example.com',
            'preferred_date' => Carbon::create($year, 2, 1),
            'status' => 'approved',
        ]);
        Booking::factory()->create([
            'reference_number' => 'BK-JUAN-001',
            'employee_id' => $other->id,
            'full_name' => 'Juan Cruz',
            'email' => 'juan@example.com',
            'preferred_date' => Carbon::create($year, 2, 2),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/chatbot/query', [
                'message' => "Who booked under Rodan Cruz's name?",
            ]);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'User found: Rodan Cruz')
                && str_contains($text, 'BK-RODAN-001')
                && ! str_contains($text, 'BK-JUAN-001')
                && ! str_contains($text, 'Juan Cruz'));
    }

    public function test_similar_names_are_listed_separately_instead_of_mixed(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Rodan Cruz', 'email' => 'rodan@example.com']);
        $this->createEmployee(['name' => 'Rodrigo Santos', 'email' => 'rodrigo@example.com']);

        $response = $this->actingAs($admin)
            ->postJson('/api/chatbot/query', [
                'message' => 'Is there anyone named Rod in the users?',
            ]);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'I found 2 matching records for "Rod"')
                && str_contains($text, 'Rodan Cruz')
                && str_contains($text, 'Rodrigo Santos')
                && str_contains($text, 'listed separately'));
    }

    public function test_reported_is_there_name_wording_finds_a_saved_user(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Maria Santos', 'email' => 'maria@example.com']);

        foreach ([
            'is there maria in the system saved?',
            'is there any maria recorded in the system?',
        ] as $question) {
            $this->actingAs($admin)
                ->postJson('/api/chatbot/query', ['message' => $question])
                ->assertOk()
                ->assertJsonPath('response', fn (string $text) => str_contains($text, 'User found: Maria Santos'));
        }
    }

    public function test_reported_is_there_name_wording_finds_a_booking_contact(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $year = now()->year;

        Booking::factory()->create([
            'reference_number' => 'BK-MARIA-001',
            'full_name' => 'Maria Dela Cruz',
            'email' => 'maria.delacruz@example.com',
            'preferred_date' => Carbon::create($year, 6, 10),
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'is there any maria recorded in the system?'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Booking contact found: Maria Dela Cruz')
                && str_contains($text, 'BK-MARIA-001'));
    }

    public function test_reported_is_there_name_wording_reports_no_records_for_unknown_name(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);

        $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'is there any zzyzx recorded in the system?'])
            ->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'No matching records were found for "zzyzx"'));
    }

    public function test_inventory_phrase_is_not_mistaken_for_a_person_name(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $admin = $this->createAdmin(['name' => 'System Admin']);
        $this->createEmployee(['name' => 'Lowell Santos', 'email' => 'lowell@example.com']);
        $this->createInventory(['quantity' => 1, 'low_stock_threshold' => 5, 'status' => 'available']);

        $response = $this->actingAs($admin)
            ->postJson('/api/chatbot/query', ['message' => 'Check low stock']);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => ! str_contains($text, 'User found: Lowell Santos')
                && str_contains($text, 'Low stock items')
                && str_contains($text, 'LOW STOCK'));
    }

    public function test_unknown_person_states_clearly_that_no_records_were_found(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $response = $this->actingAs($this->createAdmin())
            ->postJson('/api/chatbot/query', [
                'message' => 'Is there anyone named Zzyzx in the users?',
            ]);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'No matching records were found for "Zzyzx"'));
    }

    public function test_missing_person_name_asks_for_the_name_instead_of_guessing(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $response = $this->actingAs($this->createAdmin())
            ->postJson('/api/chatbot/query', [
                'message' => "Who booked under this specific person's name?",
            ]);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, "Please tell me the person's full or partial name"));
    }

    public function test_pending_tasks_are_read_across_bookings_and_borrow_requests(): void
    {
        $this->instance(OllamaService::class, $this->offlineModel());

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $employee = $this->createEmployee(['name' => 'Rodan Cruz']);

        Booking::factory()->create([
            'reference_number' => 'BK-PENDING-001',
            'employee_id' => $employee->id,
            'full_name' => 'Rodan Cruz',
            'preferred_date' => Carbon::create(now()->year, 4, 1),
            'status' => 'pending',
        ]);

        BorrowRequest::factory()->create([
            'request_number' => 'BR-PENDING-001',
            'employee_id' => $employee->id,
            'status' => 'pending',
            'borrow_date' => Carbon::create(now()->year, 4, 2),
            'due_date' => Carbon::create(now()->year, 4, 9),
        ]);

        $response = $this->postJson('/api/chatbot/query', ['message' => 'What tasks are pending?']);

        $response->assertOk()
            ->assertJsonPath('sources.0', 'db:bookings')
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Bookings awaiting action: 1')
                && str_contains($text, 'Borrow requests awaiting action: 1')
                && str_contains($text, 'BK-PENDING-001')
                && str_contains($text, 'BR-PENDING-001'));
    }

    public function test_returned_items_come_from_borrow_items_not_from_the_model(): void
    {
        $model = $this->offlineModel();
        $this->instance(OllamaService::class, $model);

        $this->actingAs($this->createAdmin(['name' => 'System Admin']));
        $employee = $this->createEmployee(['name' => 'Rodan Cruz']);
        $inventory = $this->createInventory(['name' => 'Torque Wrench', 'quantity' => 2]);

        $borrow = BorrowRequest::factory()->create([
            'request_number' => 'BR-RETURN-001',
            'employee_id' => $employee->id,
            'status' => 'returned',
            'borrow_date' => Carbon::create(now()->year, 5, 1),
            'due_date' => Carbon::create(now()->year, 5, 8),
            'return_date' => Carbon::create(now()->year, 5, 7),
            'returned_at' => Carbon::create(now()->year, 5, 7, 15, 0),
        ]);

        BorrowItem::factory()->create([
            'borrow_request_id' => $borrow->id,
            'inventory_id' => $inventory->id,
            'quantity' => 1,
            'is_returned' => true,
            'returned_at' => Carbon::create(now()->year, 5, 7, 15, 0),
        ]);

        $response = $this->postJson('/api/chatbot/query', ['message' => 'Show me all returned items']);

        $response->assertOk()
            ->assertJsonPath('response', fn (string $text) => str_contains($text, 'Returned borrow requests: 1')
                && str_contains($text, 'BR-RETURN-001')
                && str_contains($text, 'Torque Wrench'));

        $this->assertSame([], $model->calls);
    }
}
