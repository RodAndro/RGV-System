<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InventoryQrTest extends TestCase
{
    use RefreshDatabase;

    /**
     * RefreshDatabase rolls each test back, so a role cached by an earlier
     * test can outlive the row it points at. Clearing the registrar cache
     * keeps this test independent of execution order.
     */
    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

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
        $jpeg = $response->getContent();
        $imageInfo = getimagesizefromstring($jpeg);
        $this->assertIsArray($imageInfo);
        $this->assertSame(IMAGETYPE_JPEG, $imageInfo[2]);
        $this->assertSame(600, $imageInfo[0]);
        $this->assertSame(692, $imageInfo[1]);

        // The item code is drawn centred in the band below the QR code.
        $image = imagecreatefromstring($jpeg);
        $darkPixels = 0;
        $minX = $imageInfo[0];
        $maxX = 0;
        for ($y = 600; $y < $imageInfo[1]; $y++) {
            for ($x = 0; $x < $imageInfo[0]; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;
                if ($red < 100 && $green < 100 && $blue < 100) {
                    $darkPixels++;
                    $minX = min($minX, $x);
                    $maxX = max($maxX, $x);
                }
            }
        }
        imagedestroy($image);

        $this->assertGreaterThan(500, $darkPixels);
        $this->assertLessThanOrEqual(12, abs((($minX + $maxX) / 2) - 300));

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
