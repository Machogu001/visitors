<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Services\BrowserInstaller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class InstallController extends Controller
{
    public function create(Request $request, BrowserInstaller $installer): View|RedirectResponse
    {
        if ($installer->isInstalled()) {
            return redirect()->route('login');
        }

        return $this->view($request);
    }

    public function store(Request $request, BrowserInstaller $installer): View|RedirectResponse
    {
        if ($installer->isInstalled()) {
            return redirect()->route('login');
        }

        abort_unless($installer->tokenIsValid((string) $request->input('_installer_token')), 403);

        $validator = validator($request->all(), [
            'app_name' => ['required', 'string', 'max:80'],
            'app_url' => ['required', 'url:http,https', 'max:255'],
            'app_timezone' => ['required', Rule::in(timezone_identifiers_list())],
            'app_locale' => ['required', Rule::in(['en', 'de', 'fr', 'cs'])],
            'db_connection' => ['required', Rule::in(['mariadb', 'mysql', 'sqlite'])],
            'db_host' => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_port' => ['required_unless:db_connection,sqlite', 'nullable', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'site_name' => ['required', 'string', 'max:255'],
            'site_address' => ['nullable', 'string', 'max:500'],
            'admin_first_name' => ['required', 'string', 'max:255'],
            'admin_last_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'confirmed', 'min:12', Rule::notIn(['password', 'ChangeMe-42!', 'changeme-42!'])],
        ]);

        if ($validator->fails()) {
            return $this->view($request, $validator->errors()->all(), $request->except(['admin_password', 'admin_password_confirmation', 'db_password']));
        }

        try {
            $installer->install($validator->validated());
        } catch (Throwable $exception) {
            Log::error('Browser installation failed.', ['exception' => $exception]);

            return $this->view(
                $request,
                ['Installation could not be completed. Check the database details and writable file permissions, then try again.'],
                $request->except(['admin_password', 'admin_password_confirmation', 'db_password']),
            );
        }

        return redirect()->route('login', ['installed' => 1]);
    }

    /**
     * @param  list<string>  $errors
     * @param  array<string, mixed>  $values
     */
    private function view(Request $request, array $errors = [], array $values = []): View
    {
        return view('public.install', [
            'installAction' => route('install.store'),
            'installerToken' => app(BrowserInstaller::class)->issueToken(),
            'installErrors' => $errors,
            'values' => $values,
            'defaults' => [
                'app_name' => config('app.name', 'VisitorPortal'),
                'app_url' => $request->getSchemeAndHttpHost(),
                'app_timezone' => config('app.timezone', 'UTC'),
                'app_locale' => config('app.locale', 'en'),
                'db_connection' => config('database.default', 'mariadb'),
                'db_host' => config('database.connections.'.config('database.default').'.host', 'db'),
                'db_port' => (string) config('database.connections.'.config('database.default').'.port', '3306'),
                'db_database' => config('database.connections.'.config('database.default').'.database', 'visitorportal'),
                'db_username' => config('database.connections.'.config('database.default').'.username', 'visitor'),
                'site_name' => 'Main Site',
            ],
        ]);
    }
}
