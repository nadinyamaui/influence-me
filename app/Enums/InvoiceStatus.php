<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public static function pendingValues(): array
    {
        return [
            self::Sent->value,
            self::Overdue->value,
        ];
    }
}
