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

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
            self::Sent => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200',
            self::Paid => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
            self::Overdue => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
            self::Cancelled => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
        };
    }
}
