<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Site;
use App\Models\User;
use App\Rules\ActiveSite;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->label(__('Standort'))
                    ->options(fn (): array => Site::query()->active()->orderBy('name')->pluck('name', 'id')->all())
                    ->default(fn () => Site::query()->active()->value('id') ?: Site::default()->id)
                    ->required()
                    ->live()
                    ->rules([new ActiveSite])
                    ->preload(),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, callable $get): Unique => $rule->where('site_id', $get('site_id'))),
                TextInput::make('description')
                    ->label(__('Beschreibung'))
                    ->placeholder(__('z. B. Personalwesen, IT-Support, Einkauf'))
                    ->maxLength(255),
                TextInput::make('location')
                    ->label(__('Gebäude/Raum')),
                Select::make('head_user_id')
                    ->label(__('Abteilungsleiter'))
                    ->options(fn (callable $get): array => self::userOptionsForSite((int) ($get('site_id') ?: Site::default()->id)))
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('receptionist_user_id')
                    ->label(__('Dedizierte Empfangskraft / Direktionsassistent'))
                    ->options(fn (callable $get): array => self::userOptionsForSite((int) ($get('site_id') ?: Site::default()->id)))
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Toggle::make('allow_public_booking')
                    ->label(__('Öffentliche Buchung erlauben'))
                    ->default(true),
                Toggle::make('requires_approval')
                    ->label(__('Erfordert Genehmigung durch Gastgeber/Leitung'))
                    ->default(true),
                Toggle::make('has_dedicated_reception')
                    ->label(__('Direktionsbereich (Eigene Empfangsstufe)'))
                    ->default(false),
                Toggle::make('is_finance_department')
                    ->label(__('Finanzabteilung (Scheckausgabe / -annahme)'))
                    ->default(false),
                Toggle::make('is_active')
                    ->label(__('Aktiv'))
                    ->default(true)
                    ->required(),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function userOptionsForSite(int $siteId): array
    {
        return User::query()
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['welcome monitor', 'welcome_monitor']))
            ->where(function ($query) use ($siteId): void {
                $query->where('site_id', $siteId)
                    ->orWhereHas('sites', fn ($query) => $query->whereKey($siteId));
            })
            ->orderBy('first_name')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => $user->fullName])
            ->all();
    }
}
