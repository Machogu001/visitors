<?php

namespace App\Filament\Resources\AuditEvents\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class AuditEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label(__('Time'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event')
                    ->label(__('Event'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('actor.fullName')
                    ->label(__('Actor'))
                    ->placeholder(__('System'))
                    ->searchable(['first_name', 'name']),
                TextColumn::make('site.name')
                    ->label(__('Site'))
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('auditable_type')
                    ->label(__('Record type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),
                TextColumn::make('auditable_id')
                    ->label(__('Record ID'))
                    ->placeholder('—'),
                TextColumn::make('subject_id')
                    ->label(__('Subject ID'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('Event'))
                    ->options(fn (): array => \App\Models\AuditEvent::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all()),
                SelectFilter::make('site_id')
                    ->label(__('Site'))
                    ->relationship('site', 'name'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}