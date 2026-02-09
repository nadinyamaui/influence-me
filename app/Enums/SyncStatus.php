<?php

namespace App\Enums;

enum SyncStatus: string
{
    case Idle = 'idle';
    case Syncing = 'syncing';
    case Failed = 'failed';
}
