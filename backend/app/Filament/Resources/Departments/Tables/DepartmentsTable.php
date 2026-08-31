<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label(__('Standort'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('headUser.name')
                    ->label(__('Abteilungsleiter'))
                    ->formatStateUsing(fn ($record) => $record->headUser?->fullName ?? '-')
                    ->searchable(['first_name', 'name']),
                TextColumn::make('receptionist.name')
                    ->label(__('Dedizierter Empfang'))
                    ->formatStateUsing(fn ($record) => $record->receptionist?->fullName ?? '-')
                    ->searchable(['first_name', 'name'])
                    ->toggleable(),
                TextColumn::make('location')
                    ->label(__('Bereich/Ort'))
                    ->searchable(),
                IconColumn::make('allow_public_booking')
                    ->label(__('Buchbar'))
                    ->boolean(),
                IconColumn::make('requires_approval')
                    ->label(__('Freigabepflichtig'))
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_finance_department')
                    ->label(__('Finanzen'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('Aktiv'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
