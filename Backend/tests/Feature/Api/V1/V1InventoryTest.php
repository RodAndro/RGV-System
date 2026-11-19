<?php

namespace Tests\Feature\Api\V1;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class V1InventoryTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();
        $this->seedCategories();
    }

    /**
     * InventoryFactory picks a category by slug, so the catalogue the
     * seeder creates must exist before the factory runs.
     */
    private function seedCategories(): void
    {
        foreach ([
            'tools' => 'Tools',
            'materials' => 'Materials',
            'electrical' => 'Electrical',
            'plumbing' => 'Plumbing',
            'safety-equipment' => 'Safety Equipment',
        ] as $slug => $name) {
            InventoryCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true],
            );
        }
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/inventories')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_employee_can_list_inventory_with_meta(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        $this->createInventory(['name' => 'Adjustable Wrench']);

        $this->getJson('/api/v1/inventories')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'item_code', 'name', 'status', 'category']],
                'meta' => ['page', 'limit', 'total', 'total_pages'],
            ])
            ->assertJsonPath('meta.page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_supports_pagination(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        Inventory::factory()->count(5)->create(['status' => 'available', 'is_active' => true]);

        $this->getJson('/api/v1/inventories?limit=2&page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.limit', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.total_pages', 3);
    }

    public function test_index_supports_search_filter_and_sort(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        $this->createInventory(['name' => 'Pipe Wrench', 'status' => 'available']);
        $this->createInventory(['name' => 'Safety Helmet', 'status' => 'maintenance']);

        $this->getJson('/api/v1/inventories?q=pipe')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Pipe Wrench');

        $this->getJson('/api/v1/inventories?status=maintenance')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Safety Helmet');

        $sorted = $this->getJson('/api/v1/inventories?sort=name')->assertOk();
        $names = array_column($sorted->json('data'), 'name');
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    public function test_show_returns_one_item(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        $inventory = $this->createInventory();

        $this->getJson('/api/v1/inventories/'.$inventory->id)
            ->assertOk()
            ->assertJsonPath('data.id', $inventory->id);
    }

    public function test_show_returns_404_envelope_for_missing_item(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);

        $this->getJson('/api/v1/inventories/999999')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_employee_cannot_create_inventory(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);

        $this->postJson('/api/v1/inventories', $this->payload())
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_admin_can_create_inventory(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);

        $response = $this->postJson('/api/v1/inventories', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Extension Cord')
            ->assertJsonPath('data.quantity', 12);

        $this->assertNotNull($response->headers->get('Location'));
        $this->assertDatabaseHas('inventories', ['item_code' => 'INV-9001']);
    }

    public function test_create_validation_returns_field_details(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->postJson('/api/v1/inventories', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonFragment(['field' => 'item_code'])
            ->assertJsonFragment(['field' => 'name']);
    }

    public function test_create_rejects_duplicate_item_code(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);
        $this->createInventory(['item_code' => 'INV-9001']);

        $this->postJson('/api/v1/inventories', $this->payload())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_admin_can_update_inventory(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);
        $inventory = $this->createInventory();

        $this->patchJson('/api/v1/inventories/'.$inventory->id, ['quantity' => 42])
            ->assertOk()
            ->assertJsonPath('data.quantity', 42);
    }

    public function test_admin_can_delete_inventory(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);
        $inventory = $this->createInventory();

        $this->deleteJson('/api/v1/inventories/'.$inventory->id)
            ->assertNoContent();

        $this->assertSoftDeleted('inventories', ['id' => $inventory->id]);
    }

    public function test_employee_cannot_delete_inventory(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        $inventory = $this->createInventory();

        $this->deleteJson('/api/v1/inventories/'.$inventory->id)
            ->assertStatus(403);
    }

    public function test_categories_are_listed_with_pagination_meta(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);
        InventoryCategory::create(['name' => 'Consumables', 'slug' => 'consumables', 'is_active' => true]);

        $this->getJson('/api/v1/inventory-categories')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'slug']], 'meta'])
            ->assertJsonPath('meta.total', 6);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $category = InventoryCategory::firstOrCreate(
            ['slug' => 'tools'],
            ['name' => 'Tools', 'is_active' => true],
        );

        return array_merge([
            'item_code' => 'INV-9001',
            'name' => 'Extension Cord',
            'description' => 'Heavy duty extension cord',
            'category_id' => $category->id,
            'quantity' => 12,
            'unit' => 'pcs',
            'unit_cost' => 24.50,
            'status' => 'available',
            'condition' => 'new',
            'location' => 'Main Warehouse',
            'low_stock_threshold' => 3,
            'date_added' => '2026-11-01',
        ], $overrides);
    }
}
