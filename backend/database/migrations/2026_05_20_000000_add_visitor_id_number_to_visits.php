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
            if (! Schema::hasColumn('visits', 'visitor_id_number')) {
                $column = $table->string('visitor_id_number')->nullable();

                if (Schema::hasColumn('visits', 'cheque_payee_or_drawer')) {
                    $column->after('cheque_payee_or_drawer');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'visitor_id_number')) {
                $table->dropColumn('visitor_id_number');
            }
        });
    }
};
