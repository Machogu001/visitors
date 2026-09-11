<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('health.cache_store', 'database');
        Cache::store('database')->forget(config('health.queue_heartbeat_key'));
    }

    public function test_app_health_returns_ok_without_authentication(): void
    {
        $this->getJson('/api/health/app')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'target' => 'app',
            ]);
    }

    public function test_stale_queue_health_returns_service_unavailable_without_details(): void
    {
        $this->getJson('/api/health/queue')
            ->assertServiceUnavailable()
            ->assertExactJson([
                'status' => 'fail',
                'target' => 'queue',
            ]);
    }

    public function test_unknown_health_target_is_not_found(): void
    {
        $this->getJson('/api/health/database')->assertNotFound();
    }
}