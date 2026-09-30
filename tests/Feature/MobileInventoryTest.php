<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class MobileInventoryTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_lookup_by_item_code_returns_current_details(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory([
            'item_code' => 'RGV-009',
            'name' => 'Handheld Jackhammer',
            'brand' => 'INGCO',
            'unit' => 'unit',
            'quantity' => 1,
        ]);

        Sanctum::actingAs($employee, ['employee']);

        $this->getJson('/api/v1/mobile/inventory/lookup?code=RGV-009')
            ->assertOk()
            ->assertJsonPath('item.item_code', 'RGV-009')
            ->assertJsonPath('item.brand', 'INGCO')
            ->assertJsonPath('item.unit', 'unit')
            ->assertJsonPath('item.quantity', 1);
    }

    public function test_lookup_resolves_qr_json_metadata_as_hint(): void
    {
        $employee = $this->createEmployee();
        $inventory = $this->createInventory(['item_code' => 'RGV-009', 'name' => 'Handheld Jackhammer']);

        $qrPayload = json_encode(['item_code' => 'RGV-009', 'name' => 'Stale Name', 'id' => 9999]);

        Sanctum::actingAs($employee, ['employee']);

        $this->getJson('/api/v1/mobile/inventory/lookup?code='.urlencode($qrPayload))
            ->assertOk()
            ->assertJsonPath('item.item_code', 'RGV-009')
            ->assertJsonPath('item.name', 'Handheld Jackhammer');
    }

    public function test_lookup_unknown_code_fails(): void
    {
        $employee = $this->createEmployee();

        Sanctum::actingAs($employee, ['employee']);

        $this->getJson('/api/v1/mobile/inventory/lookup?code=NOPE')
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }
}
