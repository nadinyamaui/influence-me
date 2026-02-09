<?php

namespace App\Enums;

enum ScheduledPostStatus: string
{
    case Planned = 'planned';
    case Published = 'published';
    case Cancelled = 'cancelled';
}
