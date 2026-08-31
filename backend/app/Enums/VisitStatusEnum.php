<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Enums;

enum VisitStatusEnum: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Planned = 'planned';
    case Completed = 'completed';
    case Canceled = 'canceled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::PendingApproval => __('Pending Approval'),
            self::Planned => __('Planned'),
            self::Completed => __('Completed'),
            self::Canceled => __('Cancelled'),
            self::Rejected => __('Declined'),
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $status) => $status->value, self::cases());
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }
}
