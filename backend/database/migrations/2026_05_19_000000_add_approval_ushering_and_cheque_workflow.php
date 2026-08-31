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
            if (! Schema::hasColumn('departments', 'receptionist_user_id')) {
                $table->foreignId('receptionist_user_id')
                    ->nullable()
                    ->after('head_user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('departments', 'requires_approval')) {
                $table->boolean('requires_approval')
                    ->default(true)
                    ->after('allow_public_booking');
            }

            if (! Schema::hasColumn('departments', 'is_finance_department')) {
                $table->boolean('is_finance_department')
                    ->default(false)
                    ->after('requires_approval');
            }

            if (! Schema::hasColumn('departments', 'has_dedicated_reception')) {
                $table->boolean('has_dedicated_reception')
                    ->default(false)
                    ->after('is_finance_department');
            }
        });

        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'approved_at')) {
                $table->datetime('approved_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('visits', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')
                    ->nullable()
                    ->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('visits', 'rejected_at')) {
                $table->datetime('rejected_at')->nullable()->after('approved_by_user_id');
            }

            if (! Schema::hasColumn('visits', 'rejected_by_user_id')) {
                $table->foreignId('rejected_by_user_id')
                    ->nullable()
                    ->after('rejected_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('visits', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_by_user_id');
            }

            if (! Schema::hasColumn('visits', 'ushered_at')) {
                $table->datetime('ushered_at')->nullable()->after('rejection_reason');
            }

            if (! Schema::hasColumn('visits', 'ushered_by_user_id')) {
                $table->foreignId('ushered_by_user_id')
                    ->nullable()
                    ->after('ushered_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('visits', 'cheque_action')) {
                $table->string('cheque_action', 30)->nullable()->after('ushered_by_user_id');
            }

            if (! Schema::hasColumn('visits', 'cheque_number')) {
                $table->string('cheque_number', 100)->nullable()->after('cheque_action');
            }

            if (! Schema::hasColumn('visits', 'cheque_amount')) {
                $table->decimal('cheque_amount', 15, 2)->nullable()->after('cheque_number');
            }

            if (! Schema::hasColumn('visits', 'cheque_bank')) {
                $table->string('cheque_bank', 150)->nullable()->after('cheque_amount');
            }

            if (! Schema::hasColumn('visits', 'cheque_payee_or_drawer')) {
                $table->string('cheque_payee_or_drawer', 200)->nullable()->after('cheque_bank');
            }

            if (! Schema::hasColumn('visits', 'signature_data')) {
                $table->longText('signature_data')->nullable()->after('cheque_payee_or_drawer');
            }

            if (! Schema::hasColumn('visits', 'signed_at')) {
                $table->datetime('signed_at')->nullable()->after('signature_data');
            }

            if (! Schema::hasColumn('visits', 'signed_by_name')) {
                $table->string('signed_by_name')->nullable()->after('signed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'approved_by_user_id')) {
                $table->dropForeign(['approved_by_user_id']);
                $table->dropColumn('approved_by_user_id');
            }
            if (Schema::hasColumn('visits', 'rejected_by_user_id')) {
                $table->dropForeign(['rejected_by_user_id']);
                $table->dropColumn('rejected_by_user_id');
            }
            if (Schema::hasColumn('visits', 'ushered_by_user_id')) {
                $table->dropForeign(['ushered_by_user_id']);
                $table->dropColumn('ushered_by_user_id');
            }

            $table->dropColumn([
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'ushered_at',
                'cheque_action',
                'cheque_number',
                'cheque_amount',
                'cheque_bank',
                'cheque_payee_or_drawer',
                'signature_data',
                'signed_at',
                'signed_by_name',
            ]);
        });

        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'receptionist_user_id')) {
                $table->dropForeign(['receptionist_user_id']);
                $table->dropColumn('receptionist_user_id');
            }

            $table->dropColumn([
                'requires_approval',
                'is_finance_department',
                'has_dedicated_reception',
            ]);
        });
    }
};
