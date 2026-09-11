<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Visit;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

final class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 85;

    protected string $view = 'filament.pages.reports';

    public function getTitle(): string
    {
        return __('Visit reports');
    }

    public function getSubheading(): ?string
    {
        return __('Review visit activity and compare site performance over time.');
    }

    public function getViewData(): array
    {
        [$from, $to] = $this->dateRange();
        $visits = Visit::query()
            ->with('site:id,name')
            ->withCount('visitors')
            ->whereBetween('scheduled_from', [$from, $to])
            ->orderBy('scheduled_from')
            ->get();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'stats' => [
                'visits' => $visits->count(),
                'participants' => $visits->sum('visitors_count'),
                'completed' => $visits->where('status', 'completed')->count(),
                'canceled' => $visits->where('status', 'canceled')->count(),
            ],
            'daily' => $visits->groupBy(fn (Visit $visit) => $visit->scheduled_from->toDateString())
                ->map->count()
                ->sortKeys(),
            'sites' => $visits->groupBy(fn (Visit $visit) => $visit->site?->name ?? __('Unknown site'))
                ->map(fn ($siteVisits): array => [
                    'visits' => $siteVisits->count(),
                    'participants' => $siteVisits->sum('visitors_count'),
                    'completed' => $siteVisits->where('status', 'completed')->count(),
                    'canceled' => $siteVisits->where('status', 'canceled')->count(),
                ])
                ->sortByDesc('visits'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->is_active && $user->hasAnyRole(['admin', 'super_admin']);
    }

    private function dateRange(): array
    {
        $to = filled(request('to'))
            ? rescue(fn () => Carbon::parse(request('to')), now(), report: false)
            : now();
        $to = $to->endOfDay();
        $from = filled(request('from'))
            ? rescue(fn () => Carbon::parse(request('from')), $to->copy()->subDays(30), report: false)
            : $to->copy()->subDays(30);
        $from = $from->startOfDay();

        if ($from->gt($to) || $from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(30)->startOfDay();
        }

        return [$from, $to];
    }
}