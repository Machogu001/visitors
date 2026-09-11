<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BrowserInstallerTest extends TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir().'/visitorportal-installer-'.bin2hex(random_bytes(6));
        mkdir($this->temporaryDirectory, 0755, true);
        copy(base_path('.env.example'), $this->environmentPath());
        touch($this->databasePath());

        config([
            'installer.environment_file' => $this->environmentPath(),
            'installer.lock_file' => $this->temporaryDirectory.'/installed.lock',
            'installer.token_file' => $this->temporaryDirectory.'/installer.token',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->databasePath(),
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->temporaryDirectory) && is_dir($this->temporaryDirectory)) {
            foreach (glob($this->temporaryDirectory.'/*') ?: [] as $path) {
                @unlink($path);
            }

            @rmdir($this->temporaryDirectory);
        }

        parent::tearDown();
    }

    public function test_install_page_is_public_and_contains_all_setup_steps(): void
    {
        $this->get(route('install'))
            ->assertOk()
            ->assertSee('Deploy your portal')
            ->assertSee('Application')
            ->assertSee('Database')
            ->assertSee('First site')
            ->assertSee('Administrator')
            ->assertSee('Finish installation')
            ->assertSee('name="_installer_token"', false);
    }

    public function test_install_submission_rejects_an_invalid_token(): void
    {
        $this->post(route('install.store'), ['_installer_token' => 'invalid'])
            ->assertForbidden();
    }

    public function test_configured_application_fails_closed_when_database_is_unavailable(): void
    {
        config([
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'database.default' => 'unavailable',
            'database.connections.unavailable' => [
                'driver' => 'sqlite',
                'database' => $this->temporaryDirectory.'/missing/database.sqlite',
                'prefix' => '',
            ],
        ]);
        $this->get(route('install'))->assertRedirect(route('login'));
    }

    public function test_browser_installer_deploys_an_empty_database_and_locks_itself(): void
    {
        $this->get(route('install'))->assertOk();
        $token = trim((string) file_get_contents($this->temporaryDirectory.'/installer.token'));

        $this->post(route('install.store'), $this->validPayload($token))
            ->assertRedirect(route('login', ['installed' => 1]));

        $site = Site::query()->where('slug', Site::DEFAULT_SLUG)->firstOrFail();
        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertSame('Head Office', $site->name);
        $this->assertSame('Nairobi', $site->address);
        $this->assertSame($site->id, $admin->site_id);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue(Hash::check('VerySecurePass123!', $admin->password));
        $this->assertFileExists($this->temporaryDirectory.'/installed.lock');
        $this->assertFileDoesNotExist($this->temporaryDirectory.'/installer.token');

        $environment = (string) file_get_contents($this->environmentPath());
        $this->assertStringContainsString('APP_NAME="Philmed Visitors"', $environment);
        $this->assertStringContainsString('APP_ENV="production"', $environment);
        $this->assertStringContainsString('APP_DEBUG="false"', $environment);
        $this->assertMatchesRegularExpression('/^APP_KEY="base64:[A-Za-z0-9+\/=]+"$/m', $environment);
        $this->assertStringContainsString('DB_CONNECTION="sqlite"', $environment);
        $this->assertStringNotContainsString('VerySecurePass123!', $environment);

        $this->get(route('login', ['installed' => 1]))
            ->assertOk()
            ->assertSee('Installation complete. Sign in with the administrator account you just created.');
        $this->followingRedirects()->get(route('install'))->assertOk()->assertSee('Login');
        $this->post(route('install.store'), $this->validPayload($token))->assertRedirect(route('login'));
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(string $token): array
    {
        return [
            '_installer_token' => $token,
            'app_name' => 'Philmed Visitors',
            'app_url' => 'https://visitor.example.test',
            'app_timezone' => 'Africa/Nairobi',
            'app_locale' => 'en',
            'db_connection' => 'sqlite',
            'db_host' => '',
            'db_port' => '',
            'db_database' => $this->databasePath(),
            'db_username' => '',
            'db_password' => '',
            'site_name' => 'Head Office',
            'site_address' => 'Nairobi',
            'admin_first_name' => 'Portal',
            'admin_last_name' => 'Admin',
            'admin_email' => 'admin@example.test',
            'admin_password' => 'VerySecurePass123!',
            'admin_password_confirmation' => 'VerySecurePass123!',
        ];
    }

    private function environmentPath(): string
    {
        return $this->temporaryDirectory.'/.env';
    }

    private function databasePath(): string
    {
        return $this->temporaryDirectory.'/database.sqlite';
    }
}
