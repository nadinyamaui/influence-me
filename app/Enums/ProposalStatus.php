<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Revised = 'revised';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Revised => 'Revised',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
            self::Sent => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
            self::Approved => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
            self::Revised => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
        };
    }
}
