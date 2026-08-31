<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Services\PublicBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublicBookingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_booking_requires_phone_company_and_topic(): void
    {
        $site = Site::factory()->create([
            'timezone' => 'Africa/Nairobi',
            'allow_general_booking' => true,
        ]);

        $host = User::factory()->create([
            'site_id' => $site->id,
            'is_active' => true,
        ]);

        $site->update(['general_booking_host_id' => $host->id]);

        $validPayload = [
            'site_id' => $site->id,
            'booking_type' => 'general',
            'department_id' => null,
            'purpose' => 'General consultation & information',
            'date' => Carbon::now('Africa/Nairobi')->addWeek()->format('Y-m-d'),
            'time' => '09:00',
            'duration_minutes' => 30,
            'salutation' => 'mr',
            'first_name' => 'Eric',
            'name' => 'Machogu',
            'email' => 'eric@example.com',
            'phone' => '+254 700 000000',
            'company' => 'BreMac Consultant Limited',
            'notes' => 'Meeting with the host',
        ];

        foreach (['phone', 'company', 'notes'] as $requiredField) {
            try {
                app(PublicBookingService::class)->createBooking([
                    ...$validPayload,
                    $requiredField => '',
                ]);

                $this->fail("Public booking was created without {$requiredField}.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($requiredField, $exception->errors());
            }
        }

        $this->assertDatabaseCount('visits', 0);
        $this->assertDatabaseCount('visitors', 0);
    }
}
