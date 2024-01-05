<?php declare(strict_types=1);

namespace App\Enum;

enum TimeframeEnum: string
{
    case S1 = '1s';
    case H1 = '1h';
    case M1 = '1m';
    case D1 = '1d';
}
