<?php

namespace App\Filament\Resources\AuditEvents\Tables;

use App\Models\AuditEvent;
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
                    ->formatStateUsing(fn (string $state): string => self::eventLabel($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('actor.fullName')
                    ->label(__('Actor'))
                    ->placeholder(__('System'))
                    ->searchable(['first_name', 'name']),
                TextColumn::make('site.name')
                    ->label(__('Site'))
                    ->placeholder(__('All sites'))
                    ->sortable(),
                TextColumn::make('details')
                    ->label(__('Details'))
                    ->state(fn (AuditEvent $record): string => self::details($record))
                    ->wrap(),
                TextColumn::make('auditable_type')
                    ->label(__('Record type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('auditable_id')
                    ->label(__('Record ID'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        ->pluck('event')
                        ->mapWithKeys(fn (string $event): array => [$event => self::eventLabel($event)])
                        ->all()),
                SelectFilter::make('site_id')
                    ->label(__('Site'))
                    ->relationship('site', 'name'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    private static function eventLabel(string $event): string
    {
        return match ($event) {
            'export.roll_call' => __('Emergency roll call exported'),
            'export.visit_report' => __('Visit report exported'),
            default => str($event)->replace(['.', '_'], ' ')->headline()->toString(),
        };
    }

    private static function details(AuditEvent $event): string
    {
        $metadata = $event->metadata ?? [];
        $rowCount = (int) ($metadata['row_count'] ?? 0);

        return match ($event->event) {
            'export.roll_call' => trans_choice(':count person|:count people', $rowCount, ['count' => $rowCount]),
            'export.visit_report' => __(':from to :to; :count visits', [
                'from' => $metadata['from'] ?? '—',
                'to' => $metadata['to'] ?? '—',
                'count' => $rowCount,
            ]),
            default => '—',
        };
    }
}