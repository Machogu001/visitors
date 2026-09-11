<?php

namespace Tests\Feature;

use App\Enums\VisitStatusEnum;
use App\Models\AuditEvent;
use App\Models\Visit;
use App\Models\Visitor;
use App\Support\SelfCheckInUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SelfCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['reception.self_check_in.enabled' => true]);
    }

    public function test_participant_can_check_in_with_valid_signed_link(): void
    {
        Notification::fake();
        $visitor = Visitor::factory()->create();
        $visit = Visit::factory()->create([
            'status' => VisitStatusEnum::Planned->value,
            'scheduled_from' => now()->subMinutes(15),
            'scheduled_until' => now()->addHour(),
        ]);
        $visit->visitors()->attach($visitor);
        $url = SelfCheckInUrl::generate($visit, $visitor);

        $this->get($url)->assertOk()->assertSeeText('Confirm check-in');
        $this->post($url)->assertRedirect();

        $this->assertNotNull($visit->visitors()->whereKey($visitor->id)->firstOrFail()->pivot->checked_in_at);
        $this->assertSame(1, AuditEvent::query()->where('event', 'visit.participant.checked_in')->count());
    }

    public function test_modified_or_unrelated_signed_link_is_rejected(): void
    {
        $visitor = Visitor::factory()->create();
        $otherVisitor = Visitor::factory()->create();
        $visit = Visit::factory()->create();
        $visit->visitors()->attach($visitor);
        $url = str_replace('/'.$visitor->id.'?', '/'.$otherVisitor->id.'?', SelfCheckInUrl::generate($visit, $visitor));

        $this->get($url)->assertForbidden();
    }
}