<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use App\Models\BorrowReturnEvidence;
use App\Notifications\BorrowRequestReturned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class MobileReturnTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_return_without_photo_restores_stock_and_stores_location_and_time(): void
    {
        [$employee, $request, $item, $inventory] = $this->borrowedScenario();

        Sanctum::actingAs($employee, ['employee']);

        $response = $this->postJson("/api/v1/mobile/borrow-requests/{$request->id}/return", [
            'idempotency_key' => '5f5f5f5f-0000-4000-8000-000000000001',
            'return_location' => 'Main Warehouse',
            'items' => [
                ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('request.status', 'returned');

        $this->assertSame(2, $inventory->fresh()->quantity);
        $this->assertTrue((bool) $item->fresh()->is_returned);
        $this->assertSame('Main Warehouse', $request->fresh()->return_location);
        $this->assertNotNull($request->fresh()->returned_at);
        $this->assertNotNull($item->fresh()->returned_at);
        $this->assertSame(0, BorrowReturnEvidence::count());
        Notification::assertSentTo(
            $employee,
            BorrowRequestReturned::class,
            fn (BorrowRequestReturned $notification) => $notification->borrowRequest->returned_at !== null,
        );
    }

    public function test_return_is_idempotent_on_retry(): void
    {
        [$employee, $request, $item, $inventory] = $this->borrowedScenario();

        Sanctum::actingAs($employee, ['employee']);

        $payload = [
            'idempotency_key' => '5f5f5f5f-0000-4000-8000-000000000002',
            'return_location' => 'Main Warehouse',
            'items' => [
                ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
            ],
        ];

        $this->postJson("/api/v1/mobile/borrow-requests/{$request->id}/return", $payload)->assertOk();
        $this->postJson("/api/v1/mobile/borrow-requests/{$request->id}/return", $payload)->assertOk();

        $this->assertSame(2, $inventory->fresh()->quantity, 'Stock must not be restored twice.');
        $this->assertSame(0, BorrowReturnEvidence::count());
    }

    public function test_return_requires_a_location(): void
    {
        [$employee, $request, $item, $inventory] = $this->borrowedScenario();

        Sanctum::actingAs($employee, ['employee']);

        $this->postJson("/api/v1/mobile/borrow-requests/{$request->id}/return", [
            'idempotency_key' => '5f5f5f5f-0000-4000-8000-000000000003',
            'items' => [
                ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('return_location');

        $this->assertFalse((bool) $item->fresh()->is_returned);
        $this->assertSame(0, $inventory->fresh()->quantity, 'Stock must not be restored when upload fails.');
        $this->assertSame(0, BorrowReturnEvidence::count());
    }

    private function borrowedScenario(): array
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['quantity' => 2]);

        $request = BorrowRequest::create([
            'employee_id' => $employee->id,
            'status' => 'borrowed',
            'reason' => 'Test',
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'borrowed_at' => now(),
        ]);

        $item = $request->borrowItems()->create([
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'reserved_quantity' => 2,
            'is_returned' => false,
        ]);

        $inventory->decrement('quantity', 2);

        return [$employee, $request, $item, $inventory];
    }
}
