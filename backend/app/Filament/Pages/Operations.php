<?php

namespace App\Filament\Pages;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\OccupancyService;
use BackedEnum;
use Filament\Pages\Page;

final class Operations extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Operations';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.operations';

    public function getTitle(): string
    {
        return __('Operations and emergency roll call');
    }

    public function getSubheading(): ?string
    {
        return __('Live occupancy, overdue check-outs, and emergency roll-call tools.');
    }

    public function getViewData(): array
    {
        $occupants = app(OccupancyService::class)->current();

        return [
            'occupants' => $occupants,
            'stats' => [
                'inside' => $occupants->count(),
                'overdue' => $occupants->where('overdue', true)->count(),
                'sites' => $occupants->pluck('site_id')->filter()->unique()->count(),
                'eventsToday' => AuditEvent::query()->where('occurred_at', '>=', now()->startOfDay())->count(),
            ],
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->is_active
            && $user->hasAnyRole(['admin', 'super_admin']);
    }
}