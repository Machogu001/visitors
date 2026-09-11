<?php

namespace Tests\Feature;

use App\Enums\VisitStatusEnum;
use App\Models\AuditEvent;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\VisitActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_check_in_creates_one_audit_event(): void
    {
        Notification::fake();
        [$visit, $visitor, $actor] = $this->visitParticipant();
        $service = app(VisitActionService::class);

        $service->checkInParticipant($visit, $visitor, $actor);
        $service->checkInParticipant($visit, $visitor, $actor);

        $this->assertDatabaseCount('audit_events', 1);
        $this->assertDatabaseHas('audit_events', [
            'event' => 'visit.participant.checked_in',
            'actor_user_id' => $actor->id,
            'site_id' => $visit->site_id,
            'auditable_type' => $visit->getMorphClass(),
            'auditable_id' => $visit->id,
            'subject_type' => $visitor->getMorphClass(),
            'subject_id' => $visitor->id,
        ]);
    }

    public function test_cheque_audit_metadata_does_not_store_sensitive_details(): void
    {
        [$visit, $visitor, $actor] = $this->visitParticipant();
        $this->actingAs($actor);

        app(VisitActionService::class)->recordChequeDetails($visit, [
            'cheque_action' => 'pick_up',
            'cheque_number' => 'SECRET-123',
            'cheque_amount' => 1250,
            'cheque_bank' => 'Sensitive Bank',
            'cheque_payee_or_drawer' => 'Private Person',
            'signature_data' => 'data:image/png;base64,SENSITIVE',
            'signed_by_name' => 'Private Person',
        ]);

        $event = AuditEvent::query()->sole();

        $this->assertSame('visit.cheque_details_recorded', $event->event);
        $this->assertSame(['cheque_action' => 'pick_up'], $event->metadata);
        $this->assertStringNotContainsString('SECRET-123', $event->toJson());
        $this->assertStringNotContainsString('SENSITIVE', $event->toJson());
    }

    /**
     * @return array{Visit, Visitor, User}
     */
    private function visitParticipant(): array
    {
        $actor = User::factory()->create();
        $visitor = Visitor::factory()->create();
        $visit = Visit::factory()->create([
            'site_id' => $actor->site_id,
            'host_user_id' => $actor->id,
            'status' => VisitStatusEnum::Planned->value,
        ]);
        $visit->visitors()->attach($visitor->id);

        return [$visit, $visitor, $actor];
    }
}