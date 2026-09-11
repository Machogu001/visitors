<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaInstallTest extends TestCase
{
    public function test_install_page_is_public_and_exposes_browser_install_action(): void
    {
        $this->get(route('install'))
            ->assertOk()
            ->assertSee('Install VisitorPortal')
            ->assertSee('data-pwa-install', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('Add to Home Screen');
    }
}