<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Tests\Feature\Filament;

use App\Filament\Pages\Branding;
use App\Models\BrandingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\PermissionHelper;
use Tests\TestCase;

class BrandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.mfa.app_required_roles' => []]);
    }

    public function test_admin_can_view_branding_page_and_non_admin_cannot(): void
    {
        $admin = (new PermissionHelper)->getIndividualUser([], 'admin');
        $receptionist = (new PermissionHelper)->getReceptionistUser();

        $this->actingAs($admin)
            ->get('/admin/branding')
            ->assertOk()
            ->assertSeeText('Portal logos')
            ->assertSeeText('Browser tab icon');

        $this->actingAs($receptionist)
            ->get('/admin/branding')
            ->assertForbidden();
    }

    public function test_admin_can_upload_logos_and_browser_tab_icon(): void
    {
        Storage::fake('public');
        $admin = (new PermissionHelper)->getIndividualUser([], 'admin');

        Livewire::actingAs($admin)
            ->test(Branding::class)
            ->set('logoLight', UploadedFile::fake()->image('light.png', 400, 120))
            ->set('logoDark', UploadedFile::fake()->image('dark.png', 400, 120))
            ->set('favicon', UploadedFile::fake()->image('tab.png', 128, 128))
            ->call('save')
            ->assertHasNoErrors();

        $branding = BrandingSetting::query()->sole();

        foreach ([$branding->logo_light_path, $branding->logo_dark_path, $branding->favicon_path] as $path) {
            $this->assertStringStartsWith('storage/branding/', $path);
            Storage::disk('public')->assertExists(substr($path, strlen('storage/')));
        }
    }

    public function test_removing_custom_icon_deletes_it_and_restores_default(): void
    {
        Storage::fake('public');
        $customPath = 'branding/custom-icon.png';
        Storage::disk('public')->put($customPath, 'image');
        BrandingSetting::query()->create(['favicon_path' => 'storage/'.$customPath]);
        config([
            'branding.favicon' => 'storage/'.$customPath,
            'branding.default_favicon' => null,
        ]);
        $admin = (new PermissionHelper)->getIndividualUser([], 'admin');

        Livewire::actingAs($admin)
            ->test(Branding::class)
            ->call('remove', 'favicon')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing($customPath);
        $this->assertNull(BrandingSetting::query()->sole()->favicon_path);
        $this->assertNull(config('branding.favicon'));
    }

    public function test_custom_favicon_is_rendered_in_browser_tab_markup(): void
    {
        $path = public_path('test-branding-favicon.png');
        file_put_contents($path, 'test-icon');

        try {
            config(['branding.favicon' => 'test-branding-favicon.png']);

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('rel="icon" href="'.asset('test-branding-favicon.png').'?v=', false)
                ->assertDontSee("asset('favicon.ico')", false);
        } finally {
            @unlink($path);
        }
    }
}
