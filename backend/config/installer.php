<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

return [
    'environment_file' => env('INSTALLER_ENV_PATH', base_path('.env')),
    'lock_file' => env('INSTALLER_LOCK_PATH', storage_path('app/installed.lock')),
    'token_file' => env('INSTALLER_TOKEN_PATH', storage_path('app/installer.token')),
];
