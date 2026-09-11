<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Support\OperationalHealth;
use Illuminate\Http\JsonResponse;
use Throwable;

final class HealthController
{
    public function __invoke(string $target, OperationalHealth $health): JsonResponse
    {
        try {
            $health->check($target);

            return response()->json([
                'status' => 'ok',
                'target' => $target,
            ]);
        } catch (Throwable) {
            return response()->json([
                'status' => 'fail',
                'target' => $target,
            ], 503);
        }
    }
}