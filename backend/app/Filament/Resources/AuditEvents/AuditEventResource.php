<?php

namespace App\Filament\Resources\AuditEvents;

use App\Filament\Resources\AuditEvents\Pages\ListAuditEvents;
use App\Filament\Resources\AuditEvents\Tables\AuditEventsTable;
use App\Models\AuditEvent;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

final class AuditEventResource extends Resource
{
    protected static ?string $model = AuditEvent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 90;

    public static function getNavigationLabel(): string
    {
        return __('Audit trail');
    }

    public static function getModelLabel(): string
    {
        return __('Audit event');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Audit trail');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->is_active
            && $user->hasAnyRole(['admin', 'super_admin']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AuditEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditEvents::route('/'),
        ];
    }
}