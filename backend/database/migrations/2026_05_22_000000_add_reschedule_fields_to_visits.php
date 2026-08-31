<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->datetime('rescheduled_at')->nullable()->after('ushered_at')->comment('When the visit was rescheduled');
            $table->foreignId('rescheduled_by_user_id')->nullable()->after('rescheduled_at')->constrained('users')->nullOnDelete()->comment('User who rescheduled the visit');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rescheduled_by_user_id');
            $table->dropColumn('rescheduled_at');
        });
    }
};
