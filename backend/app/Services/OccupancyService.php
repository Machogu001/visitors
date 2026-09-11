<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Support\Collection;

final class OccupancyService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function current(?int $siteId = null): Collection
    {
        return Visit::query()
            ->when($siteId, fn ($query) => $query->where('site_id', $siteId))
            ->with([
                'site:id,name',
                'host:id,first_name,name',
                'visitors' => fn ($query) => $query
                    ->wherePivotNotNull('checked_in_at')
                    ->wherePivotNull('checked_out_at')
                    ->orderBy('first_name')
                    ->orderBy('name'),
            ])
            ->whereHas('visitors', fn ($query) => $query
                ->whereNotNull('visit_visitor.checked_in_at')
                ->whereNull('visit_visitor.checked_out_at'))
            ->orderBy('scheduled_until')
            ->get()
            ->flatMap(fn (Visit $visit) => $visit->visitors->map(fn ($visitor): array => [
                'visit_id' => $visit->id,
                'visitor' => trim($visitor->first_name.' '.$visitor->name),
                'company' => $visitor->company,
                'host' => trim((string) $visit->host?->fullName),
                'site' => $visit->site?->name,
                'site_id' => $visit->site_id,
                'checked_in_at' => $visitor->pivot->checked_in_at,
                'scheduled_until' => $visit->scheduled_until,
                'overdue' => $visit->scheduled_until?->isPast() ?? false,
            ]))
            ->values();
    }
}