<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PermissionHelper;
use Tests\TestCase;

class SystemArchitecturePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.mfa.app_required_roles' => []]);
    }

    public function test_admin_can_view_system_architecture(): void
    {
        $admin = (new PermissionHelper)->getIndividualUser([], 'admin');

        $this
            ->actingAs($admin)
            ->get('/admin/system-architecture')
            ->assertOk()
            ->assertSeeText('System Architecture')
            ->assertSeeText('Visitor workflow')
            ->assertSeeText('Finance branch')
            ->assertSee('data-architecture-diagram', false)
            ->assertSee('flowchart LR');
    }

    public function test_non_admin_cannot_view_system_architecture(): void
    {
        $receptionist = (new PermissionHelper)->getReceptionistUser();

        $this
            ->actingAs($receptionist)
            ->get('/admin/system-architecture')
            ->assertForbidden();
    }
}