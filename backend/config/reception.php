<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

return [
    'check_in_window_hours' => (int) env('RECEPTION_CHECK_IN_WINDOW_HOURS', 48),
    'badge_preparation_window_hours' => (int) env('RECEPTION_BADGE_PREPARATION_WINDOW_HOURS', 48),
    'self_check_in' => [
        'enabled' => (bool) env('SELF_CHECK_IN_ENABLED', false),
        'window_hours' => (int) env('SELF_CHECK_IN_WINDOW_HOURS', 2),
        'grace_hours' => (int) env('SELF_CHECK_IN_GRACE_HOURS', 4),
    ],
];
