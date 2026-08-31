<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Filament\Resources\Visits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VisitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('booking_reference')
                    ->label(__('Buchungscode'))
                    ->placeholder('-'),
                TextEntry::make('host.fullName')
                    ->label(__('Host')),
                TextEntry::make('department.name')
                    ->label(__('Abteilung'))
                    ->placeholder('-'),
                TextEntry::make('substituteUser.fullName')
                    ->label(__('Vertretung'))
                    ->placeholder('-'),
                TextEntry::make('createdBy.fullName')
                    ->label(__('Erstellt von'))
                    ->placeholder('-'),
                TextEntry::make('title')
                    ->label(__('Titel')),
                TextEntry::make('purpose')
                    ->label(__('Zweck'))
                    ->placeholder('-'),
                TextEntry::make('scheduled_from')
                    ->label(__('Beginn'))
                    ->dateTime(),
                TextEntry::make('scheduled_until')
                    ->label(__('Ende'))
                    ->dateTime(),
                TextEntry::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => __('Geplant'),
                        'pending_approval' => __('Freigabe ausstehend'),
                        'draft' => __('Entwurf'),
                        'completed' => __('Abgeschlossen'),
                        'canceled' => __('Abgesagt'),
                        'rejected' => __('Abgelehnt'),
                        default => $state,
                    }),
                TextEntry::make('approvedBy.fullName')
                    ->label(__('Genehmigt von'))
                    ->placeholder('-'),
                TextEntry::make('approved_at')
                    ->label(__('Genehmigt am'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('usheredBy.fullName')
                    ->label(__('Hineingeleitet von'))
                    ->placeholder('-'),
                TextEntry::make('ushered_at')
                    ->label(__('Hineingeleitet am'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cheque_action')
                    ->label(__('Scheck-Vorgang'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pick_up' => __('Scheck abholen'),
                        'drop_off' => __('Scheck einreichen / abgeben'),
                        default => $state ?: '-',
                    }),
                TextEntry::make('cheque_number')
                    ->label(__('Scheck-Nr.'))
                    ->placeholder('-'),
                TextEntry::make('cheque_amount')
                    ->label(__('Scheckbetrag'))
                    ->money('KES')
                    ->placeholder('-'),
                TextEntry::make('cheque_bank')
                    ->label(__('Bank'))
                    ->placeholder('-'),
                TextEntry::make('signed_by_name')
                    ->label(__('Unterschrieben von'))
                    ->placeholder('-'),
                TextEntry::make('signed_at')
                    ->label(__('Unterschrieben am'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->label(__('Notizen'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('canceled_at')
                    ->label(__('Abgesagt am'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('canceledBy.fullName')
                    ->label(__('Abgesagt von'))
                    ->placeholder('-'),
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
