<?php

namespace Tests\Feature\Filament;

use App\Models\AuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PermissionHelper;
use Tests\TestCase;

class OperationsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['security.mfa.app_required_roles' => []]);
    }

    public function test_admin_can_view_operations_and_export_roll_call(): void
    {
        $admin = (new PermissionHelper)->getIndividualUser([], 'admin');

        $this->actingAs($admin)
            ->get('/admin/operations')
            ->assertOk()
            ->assertSeeText('Emergency roll call');

        $this->actingAs($admin)
            ->get(route('admin.operations.roll-call'))
            ->assertOk()
            ->assertDownload();

        $event = AuditEvent::query()->where('event', 'export.roll_call')->sole();

        $this->assertSame($admin->id, $event->actor_user_id);
        $this->assertNull($event->auditable_type);
        $this->assertNull($event->auditable_id);
        $this->assertSame(0, $event->metadata['row_count']);
    }

    public function test_non_admin_cannot_view_operations_or_export(): void
    {
        $receptionist = (new PermissionHelper)->getReceptionistUser();

        $this->actingAs($receptionist)->get('/admin/operations')->assertForbidden();
        $this->actingAs($receptionist)->get(route('admin.operations.roll-call'))->assertForbidden();
    }
}