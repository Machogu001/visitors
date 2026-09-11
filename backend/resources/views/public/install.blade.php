<x-guest-layout title="{{ __('Install VisitorPortal') }}" max-width="max-w-4xl">
    <main class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm" data-installer data-initial-step="{{ $installErrors ? 4 : 1 }}">
        <header class="border-b border-base-300/70 px-6 py-6 sm:px-9">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-primary">{{ __('VisitorPortal setup') }}</p>
                    <h1 class="mt-1 text-3xl font-semibold text-base-content">{{ __('Deploy your portal') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/65">{{ __('Complete these steps once. The installer locks automatically when setup finishes.') }}</p>
                </div>
                <div class="text-sm font-medium text-base-content/60">{{ __('Step') }} <span data-installer-step-label>{{ $installErrors ? 4 : 1 }}</span> {{ __('of') }} 4</div>
            </div>
            <div class="mt-6 grid grid-cols-4 gap-2" aria-hidden="true">
                @for ($stepNumber = 1; $stepNumber <= 4; $stepNumber++)
                    <div data-installer-progress="{{ $stepNumber }}" class="h-1.5 rounded-full {{ $stepNumber <= ($installErrors ? 4 : 1) ? 'bg-primary' : 'bg-base-300' }}"></div>
                @endfor
            </div>
        </header>

        @if ($installErrors)
            <div class="mx-6 mt-6 rounded-2xl border border-error/30 bg-error/10 p-4 text-sm text-error sm:mx-9" role="alert">
                <p class="font-semibold">{{ __('Setup needs your attention') }}</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($installErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $installAction }}" class="px-6 py-7 sm:px-9">
            <input type="hidden" name="_installer_token" value="{{ $installerToken }}">
            <section data-installer-step="1" aria-labelledby="installer-app-heading">
                <h2 id="installer-app-heading" class="text-xl font-semibold">{{ __('Application') }}</h2>
                <p class="mt-1 text-sm text-base-content/60">{{ __('Set the public identity and regional defaults.') }}</p>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <label class="form-control sm:col-span-2">
                        <span class="mb-2 text-sm font-medium">{{ __('Portal name') }}</span>
                        <input name="app_name" value="{{ $values['app_name'] ?? $defaults['app_name'] }}" required maxlength="80" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control sm:col-span-2">
                        <span class="mb-2 text-sm font-medium">{{ __('Public URL') }}</span>
                        <input name="app_url" type="url" value="{{ $values['app_url'] ?? $defaults['app_url'] }}" required class="input input-bordered w-full rounded-xl" placeholder="https://visitor.example.com">
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Timezone') }}</span>
                        <select name="app_timezone" required class="select select-bordered w-full rounded-xl">
                            @foreach (timezone_identifiers_list() as $timezone)
                                <option value="{{ $timezone }}" @selected(($values['app_timezone'] ?? $defaults['app_timezone']) === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Language') }}</span>
                        <select name="app_locale" required class="select select-bordered w-full rounded-xl">
                            @foreach (['en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français', 'cs' => 'Čeština'] as $locale => $label)
                                <option value="{{ $locale }}" @selected(($values['app_locale'] ?? $defaults['app_locale']) === $locale)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section data-installer-step="2" hidden aria-labelledby="installer-db-heading">
                <h2 id="installer-db-heading" class="text-xl font-semibold">{{ __('Database') }}</h2>
                <p class="mt-1 text-sm text-base-content/60">{{ __('Use an empty database. Existing tables are never deleted by this installer.') }}</p>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Database type') }}</span>
                        <select name="db_connection" required class="select select-bordered w-full rounded-xl">
                            @foreach (['mariadb' => 'MariaDB', 'mysql' => 'MySQL', 'sqlite' => 'SQLite'] as $connection => $label)
                                <option value="{{ $connection }}" @selected(($values['db_connection'] ?? $defaults['db_connection']) === $connection)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Database name or SQLite file') }}</span>
                        <input name="db_database" value="{{ $values['db_database'] ?? $defaults['db_database'] }}" required class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control" data-database-server-field>
                        <span class="mb-2 text-sm font-medium">{{ __('Database host') }}</span>
                        <input name="db_host" value="{{ $values['db_host'] ?? $defaults['db_host'] }}" required class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control" data-database-server-field>
                        <span class="mb-2 text-sm font-medium">{{ __('Port') }}</span>
                        <input name="db_port" type="number" min="1" max="65535" value="{{ $values['db_port'] ?? $defaults['db_port'] }}" required class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control" data-database-server-field>
                        <span class="mb-2 text-sm font-medium">{{ __('Database user') }}</span>
                        <input name="db_username" value="{{ $values['db_username'] ?? $defaults['db_username'] }}" required autocomplete="username" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control" data-database-server-field>
                        <span class="mb-2 text-sm font-medium">{{ __('Database password') }}</span>
                        <input name="db_password" type="password" data-optional="true" autocomplete="new-password" class="input input-bordered w-full rounded-xl">
                    </label>
                </div>
            </section>

            <section data-installer-step="3" hidden aria-labelledby="installer-site-heading">
                <h2 id="installer-site-heading" class="text-xl font-semibold">{{ __('First site') }}</h2>
                <p class="mt-1 text-sm text-base-content/60">{{ __('Create the primary location visitors will arrive at.') }}</p>
                <div class="mt-6 grid gap-5">
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Site name') }}</span>
                        <input name="site_name" value="{{ $values['site_name'] ?? $defaults['site_name'] }}" required maxlength="255" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Address') }} <span class="font-normal text-base-content/50">({{ __('optional') }})</span></span>
                        <textarea name="site_address" rows="3" maxlength="500" class="textarea textarea-bordered w-full rounded-xl">{{ $values['site_address'] ?? '' }}</textarea>
                    </label>
                </div>
            </section>

            <section data-installer-step="4" hidden aria-labelledby="installer-admin-heading">
                <h2 id="installer-admin-heading" class="text-xl font-semibold">{{ __('Administrator') }}</h2>
                <p class="mt-1 text-sm text-base-content/60">{{ __('This account receives full administration access. MFA setup is required after sign-in.') }}</p>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('First name') }}</span>
                        <input name="admin_first_name" value="{{ $values['admin_first_name'] ?? '' }}" required maxlength="255" autocomplete="given-name" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Last name') }}</span>
                        <input name="admin_last_name" value="{{ $values['admin_last_name'] ?? '' }}" required maxlength="255" autocomplete="family-name" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control sm:col-span-2">
                        <span class="mb-2 text-sm font-medium">{{ __('Email') }}</span>
                        <input name="admin_email" type="email" value="{{ $values['admin_email'] ?? '' }}" required maxlength="255" autocomplete="email" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Password') }}</span>
                        <input name="admin_password" type="password" required minlength="12" autocomplete="new-password" class="input input-bordered w-full rounded-xl">
                    </label>
                    <label class="form-control">
                        <span class="mb-2 text-sm font-medium">{{ __('Confirm password') }}</span>
                        <input name="admin_password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="input input-bordered w-full rounded-xl">
                    </label>
                </div>
                <div class="mt-6 rounded-2xl border border-warning/30 bg-warning/10 p-4 text-sm leading-6 text-base-content/75">
                    {{ __('Finish may take a minute while the database and permissions are prepared. Keep this page open until sign-in appears.') }}
                </div>
            </section>

            <footer class="mt-8 flex items-center justify-between border-t border-base-300/70 pt-6">
                <button type="button" data-installer-back hidden class="btn btn-ghost rounded-xl">{{ __('Back') }}</button>
                <button type="button" data-installer-next class="btn btn-primary ml-auto rounded-xl">{{ __('Next') }}</button>
                <button type="submit" data-installer-finish hidden class="btn btn-primary ml-auto rounded-xl">
                    <span data-installer-finish-label>{{ __('Finish installation') }}</span>
                    <span data-installer-spinner hidden class="loading loading-spinner loading-sm"></span>
                </button>
            </footer>
        </form>
    </main>

</x-guest-layout>
