<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Revised = 'revised';

    public static function values(): array
    {
        return array_map(
            static fn (ProposalStatus $status): string => $status->value,
            self::cases(),
        );
    }

    public static function filters(): array
    {
        return array_merge(['all'], self::values());
    }

    public static function clientViewableValues(): array
    {
        return [
            self::Sent->value,
            self::Approved->value,
            self::Rejected->value,
            self::Revised->value,
        ];
    }

    public static function clientFilters(): array
    {
        return array_merge(['all'], self::clientViewableValues());
    }
}
