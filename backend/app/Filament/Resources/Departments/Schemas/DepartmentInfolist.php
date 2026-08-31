<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('site.name')
                    ->label(__('Standort')),
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('description')
                    ->label(__('Beschreibung'))
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->label(__('Gebäude/Raum'))
                    ->placeholder('-'),
                TextEntry::make('headUser.fullName')
                    ->label(__('Abteilungsleiter'))
                    ->placeholder('-'),
                TextEntry::make('receptionist.fullName')
                    ->label(__('Dedizierter Empfang / Assistenz'))
                    ->placeholder('-'),
                IconEntry::make('allow_public_booking')
                    ->label(__('Öffentliche Buchung'))
                    ->boolean(),
                IconEntry::make('requires_approval')
                    ->label(__('Freigabepflichtig'))
                    ->boolean(),
                IconEntry::make('has_dedicated_reception')
                    ->label(__('Eigener Empfang'))
                    ->boolean(),
                IconEntry::make('is_finance_department')
                    ->label(__('Finanzabteilung'))
                    ->boolean(),
                IconEntry::make('is_active')
                    ->label(__('Aktiv'))
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(__('Erstellt am'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Aktualisiert am'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
