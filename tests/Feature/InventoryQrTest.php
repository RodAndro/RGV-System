<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_qr_uses_the_immutable_inventory_token(): void
    {
        $category = InventoryCategory::create([
            'name' => 'Tools',
            'slug' => 'tools',
            'is_active' => true,
        ]);
        $suppliedToken = 'QR-'.str_repeat('A', 32);
        $inventory = Inventory::factory()->make([
            'category_id' => $category->id,
            'item_code' => 'RGV-001',
            'name' => 'Cordless Drill',
        ]);
        $inventory->qr_token = $suppliedToken;
        $inventory->save();
        $this->assertNotSame($suppliedToken, $inventory->fresh()->qr_token);

        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get(route('admin.inventories.qrcode', ['token' => $inventory->qr_token]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('Content-Disposition', 'attachment; filename="rgv-001-cordless-drill-tools.jpg"');
        $imageInfo = getimagesizefromstring($response->getContent());
        $this->assertIsArray($imageInfo);
        $this->assertSame(IMAGETYPE_JPEG, $imageInfo[2]);

        $this->get(route('inventory.qr.show', ['token' => $inventory->qr_token]))
            ->assertOk()
            ->assertViewHas('inventory', fn (Inventory $resolved) => $resolved->is($inventory));

        $this->assertSame($inventory->qr_token, $inventory->fresh()->qr_token);
        $this->expectException(LogicException::class);
        $inventory->qr_token = 'QR-'.str_repeat('A', 32);
        $inventory->save();
    }

    public function test_qr_endpoint_rejects_invalid_tokens_and_non_admin_users(): void
    {
        $category = InventoryCategory::create([
            'name' => 'Tools',
            'slug' => 'tools',
            'is_active' => true,
        ]);
        $inventory = Inventory::factory()->create(['category_id' => $category->id]);

        $this->get(route('inventory.qr.show', ['token' => 'not-a-valid-token']))
            ->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.inventories.qrcode', ['token' => $inventory->qr_token]))
            ->assertForbidden();
    }
}