<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Resources\Sites\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('address')
                    ->label(__('Adresse'))
                    ->maxLength(255),
                TextInput::make('timezone')
                    ->label(__('Zeitzone'))
                    ->required()
                    ->default('Europe/Berlin')
                    ->maxLength(255),
                Toggle::make('allow_general_booking')
                    ->label(__('Allgemeine Buchungen erlauben'))
                    ->default(true),
                Select::make('general_booking_host_id')
                    ->label(__('Standard-Gastgeber für allgemeine Buchungen'))
                    ->options(fn ($record): array => User::query()
                        ->where('is_active', true)
                        ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['welcome monitor', 'welcome_monitor']))
                        ->when($record, function ($query, $site): void {
                            $query->where(function ($query) use ($site): void {
                                $query->where('site_id', $site->id)
                                    ->orWhereHas('sites', fn ($q) => $q->whereKey($site->id));
                            });
                        })
                        ->orderBy('first_name')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $user): array => [$user->id => $user->fullName])
                        ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Toggle::make('allow_department_booking')
                    ->label(__('Abteilungsbuchungen erlauben'))
                    ->default(true),
                Toggle::make('is_active')
                    ->label(__('Aktiv'))
                    ->default(true),
            ]);
    }
}
