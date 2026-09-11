<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;

class SystemArchitecture extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'System Architecture';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.system-architecture';

    public function getTitle(): string
    {
        return __('System Architecture');
    }

    public function getSubheading(): ?string
    {
        return __('A read-only overview of the visitor journey and the services that support it.');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->is_active
            && $user->hasAnyRole(['admin', 'super_admin']);
    }
}