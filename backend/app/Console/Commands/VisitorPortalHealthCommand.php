<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Console\Commands;

use App\Support\OperationalHealth;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

final class VisitorPortalHealthCommand extends Command
{
    protected $signature = 'visitorportal:health {target=app : app|queue|scheduler}';

    protected $description = 'Run operational health checks for VisitorPortal services.';

    public function handle(OperationalHealth $health): int
    {
        $target = strtolower((string) $this->argument('target'));

        try {
            $health->check($target);

            $this->info('OK '.$target);

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('FAIL '.$target.': '.$this->safeReason($exception));

            return Command::FAILURE;
        }
    }

    private function safeReason(Throwable $exception): string
    {
        $message = trim((string) preg_replace('/\s+/', ' ', $exception->getMessage()));

        if ($message === '') {
            return 'health check failed';
        }

        $secretValues = [
            env('APP_KEY'),
            env('DB_PASSWORD'),
            env('DB_ROOT_PASSWORD'),
            env('OIDC_CLIENT_SECRET'),
            env('MAIL_PASSWORD'),
            env('AWS_SECRET_ACCESS_KEY'),
            config('app.key'),
            config('database.connections.mysql.password'),
            config('database.connections.mariadb.password'),
            config('database.connections.pgsql.password'),
            config('database.connections.sqlsrv.password'),
            config('mail.mailers.smtp.password'),
            config('sso.oidc.client_secret'),
        ];

        foreach ($secretValues as $secretValue) {
            $value = (string) $secretValue;

            if ($value !== '' && strtolower($value) !== 'null') {
                $message = str_replace($value, '[redacted]', $message);
            }
        }

        return Str::limit($message, 160, '');
    }
}
