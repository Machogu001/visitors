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
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'head_user_id')) {
                $table->foreignId('head_user_id')
                    ->nullable()
                    ->after('location')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('departments', 'allow_public_booking')) {
                $table->boolean('allow_public_booking')
                    ->default(true)
                    ->after('is_active');
            }

            if (! Schema::hasColumn('departments', 'description')) {
                $table->text('description')
                    ->nullable()
                    ->after('name');
            }
        });

        Schema::table('sites', function (Blueprint $table) {
            if (! Schema::hasColumn('sites', 'allow_general_booking')) {
                $table->boolean('allow_general_booking')
                    ->default(true)
                    ->after('is_active');
            }

            if (! Schema::hasColumn('sites', 'general_booking_host_id')) {
                $table->foreignId('general_booking_host_id')
                    ->nullable()
                    ->after('allow_general_booking')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('sites', 'allow_department_booking')) {
                $table->boolean('allow_department_booking')
                    ->default(true)
                    ->after('general_booking_host_id');
            }
        });

        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'booking_type')) {
                $table->string('booking_type')
                    ->nullable()
                    ->after('title');
            }

            if (! Schema::hasColumn('visits', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('site_id')
                    ->constrained('departments')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('visits', 'booking_reference')) {
                $table->string('booking_reference', 32)
                    ->nullable()
                    ->unique()
                    ->after('id');
            }

            if (! Schema::hasColumn('visits', 'purpose')) {
                $table->string('purpose')
                    ->nullable()
                    ->after('booking_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'booking_reference')) {
                $table->dropUnique(['booking_reference']);
                $table->dropColumn('booking_reference');
            }

            if (Schema::hasColumn('visits', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }

            if (Schema::hasColumn('visits', 'booking_type')) {
                $table->dropColumn('booking_type');
            }

            if (Schema::hasColumn('visits', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });

        Schema::table('sites', function (Blueprint $table) {
            if (Schema::hasColumn('sites', 'general_booking_host_id')) {
                $table->dropForeign(['general_booking_host_id']);
                $table->dropColumn('general_booking_host_id');
            }

            if (Schema::hasColumn('sites', 'allow_general_booking')) {
                $table->dropColumn('allow_general_booking');
            }

            if (Schema::hasColumn('sites', 'allow_department_booking')) {
                $table->dropColumn('allow_department_booking');
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'head_user_id')) {
                $table->dropForeign(['head_user_id']);
                $table->dropColumn('head_user_id');
            }

            if (Schema::hasColumn('departments', 'allow_public_booking')) {
                $table->dropColumn('allow_public_booking');
            }

            if (Schema::hasColumn('departments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
