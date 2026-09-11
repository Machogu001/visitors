<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Services;

use App\Enums\GenderEnum;
use App\Models\Site;
use App\Models\User;
use App\Support\VisitorPortalPermissions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class BrowserInstaller
{
    public function isInstalled(): bool
    {
        if (is_file($this->lockFile())) {
            return true;
        }

        try {
            return Schema::hasTable('users') && User::query()->exists();
        } catch (Throwable) {
            return filled(config('app.key'));
        }
    }

    public function issueToken(): string
    {
        $path = (string) config('installer.token_file');
        $token = is_file($path) ? trim((string) file_get_contents($path)) : '';

        if (strlen($token) === 64) {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            throw new RuntimeException('The installer token directory could not be created.');
        }

        if (file_put_contents($path, $token.PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('The installer token could not be created.');
        }

        @chmod($path, 0600);

        return $token;
    }

    public function tokenIsValid(string $token): bool
    {
        $path = (string) config('installer.token_file');

        return is_file($path)
            && hash_equals(trim((string) file_get_contents($path)), $token);
    }

    /**
     * @param  array<string, string>  $data
     */
    public function install(array $data): void
    {
        if ($this->isInstalled()) {
            throw new RuntimeException('VisitorPortal is already installed.');
        }

        $connection = $this->connectionConfiguration($data);
        config([
            'database.default' => 'installer',
            'database.connections.installer' => $connection,
        ]);
        DB::purge('installer');
        DB::connection('installer')->getPdo();
        DB::setDefaultConnection('installer');

        $appKey = (string) (config('app.key') ?: 'base64:'.base64_encode(random_bytes(32)));
        config(['app.key' => $appKey]);
        $this->writeEnvironment($data, $appKey);

        if (Artisan::call('migrate', ['--force' => true]) !== 0) {
            throw new RuntimeException('Database migrations could not be completed.');
        }

        VisitorPortalPermissions::sync();

        DB::transaction(function () use ($data): void {
            $site = Site::default();
            $site->forceFill([
                'name' => $data['site_name'],
                'address' => $data['site_address'] ?: null,
                'timezone' => $data['app_timezone'],
                'is_active' => true,
            ])->save();

            $user = User::query()->create([
                'site_id' => $site->id,
                'first_name' => $data['admin_first_name'],
                'name' => $data['admin_last_name'],
                'email' => $data['admin_email'],
                'email_verified_at' => now(),
                'password' => Hash::make($data['admin_password']),
                'gender' => GenderEnum::Not_Specified,
                'locale' => $data['app_locale'],
                'is_active' => true,
                'local_login_allowed' => true,
            ]);
            $user->assignRole('admin');
        });

        $this->writeLock();
        @unlink((string) config('installer.token_file'));
        Artisan::call('config:clear');
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    private function connectionConfiguration(array $data): array
    {
        if ($data['db_connection'] === 'sqlite') {
            $path = $data['db_database'];

            if (! is_file($path)) {
                $directory = dirname($path);

                if (! is_dir($directory) || ! is_writable($directory) || ! touch($path)) {
                    throw new RuntimeException('The SQLite database file could not be created.');
                }
            }

            return [
                'driver' => 'sqlite',
                'database' => $path,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ];
        }

        $connection = config("database.connections.{$data['db_connection']}");
        $connection['host'] = $data['db_host'] ?? '';
        $connection['port'] = $data['db_port'] ?? '';
        $connection['database'] = $data['db_database'];
        $connection['username'] = $data['db_username'] ?? '';
        $connection['password'] = $data['db_password'] ?? '';

        return $connection;
    }

    /**
     * @param  array<string, string>  $data
     */
    private function writeEnvironment(array $data, string $appKey): void
    {
        $path = (string) config('installer.environment_file');
        $contents = is_file($path)
            ? file_get_contents($path)
            : file_get_contents(base_path('.env.example'));

        if ($contents === false || (! is_file($path) && ! is_writable(dirname($path))) || (is_file($path) && ! is_writable($path))) {
            throw new RuntimeException('The environment file is not writable.');
        }

        $values = [
            'APP_NAME' => $data['app_name'],
            'APP_ENV' => 'production',
            'APP_KEY' => $appKey,
            'APP_DEBUG' => 'false',
            'APP_URL' => rtrim($data['app_url'], '/'),
            'APP_TIMEZONE' => $data['app_timezone'],
            'APP_LOCALE' => $data['app_locale'],
            'APP_FALLBACK_LOCALE' => $data['app_locale'],
            'BRANDING_NAME' => $data['app_name'],
            'DB_CONNECTION' => $data['db_connection'],
            'DB_HOST' => $data['db_host'] ?? '',
            'DB_PORT' => $data['db_port'] ?? '',
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'] ?? '',
            'DB_PASSWORD' => $data['db_password'] ?? '',
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
        ];

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->quote((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'\s*=.*$/m';
            $contents = preg_match($pattern, $contents)
                ? preg_replace($pattern, $line, $contents, 1)
                : rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        $temporaryPath = $path.'.tmp-'.bin2hex(random_bytes(6));

        if (file_put_contents($temporaryPath, $contents, LOCK_EX) === false || ! rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('The environment file could not be updated.');
        }

        @chmod($path, 0600);
    }

    private function writeLock(): void
    {
        $path = $this->lockFile();

        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0750, true) && ! is_dir(dirname($path))) {
            throw new RuntimeException('The installation lock directory could not be created.');
        }

        $payload = json_encode(['installed_at' => now()->toIso8601String()], JSON_THROW_ON_ERROR);

        if (file_put_contents($path, $payload.PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('The installation lock could not be written.');
        }
    }

    private function lockFile(): string
    {
        return (string) config('installer.lock_file');
    }

    private function quote(string $value): string
    {
        return '"'.str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '', ''], $value).'"';
    }
}
