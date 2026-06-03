<?php

namespace Tests\Feature\Phase9;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_log_aktivitas(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);

        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'login.success',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertViewIs('admin.activity-logs.index')
            ->assertSee('login.success');
    }

    public function test_activity_logger_menyimpan_entri(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($admin);

        ActivityLogger::log('test.action', null, ['foo' => 'bar']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'test.action',
        ]);
    }
}
