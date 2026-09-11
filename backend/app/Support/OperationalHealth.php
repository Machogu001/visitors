<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class OperationalHealth
{
    public function check(string $target): void
    {
        match ($target) {
            'app' => $this->checkApp(),
            'queue' => $this->checkQueue(),
            'scheduler' => $this->checkScheduler(),
            default => throw new RuntimeException('target must be app, queue, or scheduler'),
        };
    }

    private function checkApp(): void
    {
        $this->checkDatabase();
        $this->checkWritableDirectories();
    }

    private function checkQueue(): void
    {
        $this->checkDatabase();
        $this->checkTable('jobs');
        $this->checkTable('failed_jobs');

        if (! app(OperationalHeartbeat::class)->queueIsFresh()) {
            throw new RuntimeException('heartbeat is stale');
        }
    }

    private function checkScheduler(): void
    {
        $this->checkDatabase();
        $this->checkHealthCacheStore();

        if (! app(OperationalHeartbeat::class)->schedulerIsFresh()) {
            throw new RuntimeException('heartbeat is stale');
        }
    }

    private function checkDatabase(): void
    {
        DB::connection()->getPdo();
        DB::select('select 1');
    }

    private function checkTable(string $table): void
    {
        DB::table($table)->limit(1)->exists();
    }

    private function checkHealthCacheStore(): void
    {
        $key = 'health:probe:'.Str::uuid()->toString();
        $cache = Cache::store(config('health.cache_store'));

        $cache->put($key, 'ok', now()->addMinute());

        try {
            if ($cache->get($key) !== 'ok') {
                throw new RuntimeException('health cache store is unavailable');
            }
        } finally {
            $cache->forget($key);
        }
    }

    private function checkWritableDirectories(): void
    {
        $directories = [
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($directories as $label => $path) {
            if (! is_dir($path)) {
                throw new RuntimeException($label.' is missing');
            }

            if (! is_writable($path)) {
                throw new RuntimeException($label.' is not writable');
            }
        }
    }
}