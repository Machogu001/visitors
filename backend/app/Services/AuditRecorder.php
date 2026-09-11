<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class AuditRecorder
{
    /**
     * @param  array<string, bool|float|int|string|null>  $metadata
     */
    public function record(
        string $event,
        Model $auditable,
        ?User $actor = null,
        ?Model $subject = null,
        ?int $siteId = null,
        array $metadata = [],
    ): AuditEvent {
        return AuditEvent::query()->create([
            'actor_user_id' => $actor?->getKey(),
            'site_id' => $siteId,
            'event' => $event,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }
}