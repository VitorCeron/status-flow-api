<?php

namespace App\Enums;

enum MonitorIntervalEnum: int
{
    case ONE_MINUTE   = 60;
    case FIVE_MINUTES = 300;
    case TEN_MINUTES  = 600;
}
