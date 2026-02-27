<?php

namespace App\Enums;

enum MonitorStatusEnum: string
{
    case UP = 'up';
    case DOWN = 'down';
    case UNKNOWN = 'unknown';
}
