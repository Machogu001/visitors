<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health/{target}', HealthController::class)
    ->where('target', 'app|queue|scheduler')
    ->middleware('throttle:30,1')
    ->name('health');