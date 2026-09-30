<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use App\Models\BorrowReturnEvidence;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_return_restores_stock_and_links_evidence(): void
    {
        [$employee, $request, $item, $inventory] = $this->borrowedScenario();

        $this->mock(GoogleDriveService::class, function ($mock) {
            $mock->shouldReceive('uploadEvidence')
                ->once()
                ->andReturn(['drive_file_id' => 'drive-123', 'file_name' => 'Cruz - 9/30/2026.jpg', 'mime_type' => 'image/jpeg']);
        });

        Sanctum::actingAs($employee, ['employee']);

        $response = $this->post("/api/v1/mobile/borrow-requests/{$request->id}/return", [
            'idempotency_key' => '5f5f5f5f-0000-4000-8000-000000000001',
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'items' => [
                ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('request.status', 'returned');

        $this->assertSame(2, $inventory->fresh()->quantity);
        $this->assertTrue((bool) $item->fresh()->is_returned);
        $this->assertDatabaseHas('borrow_return_evidences', [
            'borrow_request_id' => $request->id,
            'drive_file_id' => 'drive-123',
        ]);
    }

    public function test_return_is_idempotent_on_retry(): void
    {
        [$employee, $request, $item, $inventory] = $this->borrowedScenario();

        $this->mock(GoogleDriveService::class, function ($mock) {
            $mock->shouldReceive('uploadEvidence')
                ->once()
                ->andReturn(['drive_file_id' => 'drive-123', 'file_name' => 'Cruz - 9/30/2026.jpg', 'mime_type' => 'image/jpeg']);
        });

        Sanctum::actingAs($employee, ['employee']);

        $payload = [
            'idempotency_key' => '5f5f5f5f-0000-4000-8000-000000000002',
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'items' => [
                ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
            ],
        ];

        $this->post("/api/v1/mobile/borrow-requests/{$request->id}/return", $payload)->assertOk();
        $this->post("/api/v1/mobile/borrow-requests/{$request->id}/return", $payload)->assertOk();

        $this->assertSame(2, $inventory->fresh()->quantity, 'Stock must not be restored twice.');
        $this->assertSame(1, BorrowReturnEvidence::count());
    }

    public function test_upload_failure_keeps_return_incomplete(): void
    {
        [$employee, $request, $item, $inventory] = $this->borrowedScenario();

        $this->mock(GoogleDriveService::class, function ($mock) {
            $mock->shouldReceive('uploadEvidence')
                ->once()
                ->andThrow(new \RuntimeException('Drive unavailable'));
        });

        Sanctum::actingAs($employee, ['employee']);

        $this->post("/api/v1/mobile/borrow-requests/{$request->id}/return", [
            'idempotency_key' => '5f5f5f5f-0000-4000-8000-000000000003',
            'photo' => UploadedFile::fake()->image('proof.jpg'),
            'items' => [
                ['borrow_item_id' => $item->id, 'condition_returned' => 'good', 'damage_notes' => null],
            ],
        ])->assertStatus(502);

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
