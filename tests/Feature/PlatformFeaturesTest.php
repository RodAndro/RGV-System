<?php

namespace Tests\Feature;

use App\Jobs\ProcessInventoryImport;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlatformFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_import_is_logged_and_queued(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'inventory.csv',
            "item_code,name,description,category,quantity,unit_cost\nRGV-001,Hammer,Steel hammer,Tools,10,12.50\n"
        );

        $this->actingAs($user)
            ->post(route('admin.import-export.inventory.import'), [
                'file' => $file,
                'duplicate_strategy' => 'skip',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('import_logs', [
            'type' => 'inventory',
            'status' => 'queued',
            'duplicate_strategy' => 'skip',
        ]);

        Queue::assertPushed(ProcessInventoryImport::class);
    }

    public function test_audit_logs_have_valid_checksums(): void
    {
        $log = AuditLog::create([
            'event' => 'test event',
            'old_values' => ['status' => 'old'],
            'new_values' => ['status' => 'new'],
            'ip_address' => '127.0.0.1',
            'url' => 'http://localhost/test',
        ]);

        $this->assertTrue($log->fresh()->isChecksumValid());
    }

    public function test_api_rate_limit_returns_custom_429_response(): void
    {
        $category = InventoryCategory::create([
            'name' => 'Tools',
            'slug' => 'tools',
            'is_active' => true,
        ]);

        Inventory::factory()->create(['category_id' => $category->id]);

        for ($i = 0; $i < 5; $i++) {
            $this->getJson('/api/inventories')->assertOk();
        }

        $this->getJson('/api/inventories')
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJson([
                'message' => 'Too many requests.',
                'tier' => 'public',
            ]);
    }
}
