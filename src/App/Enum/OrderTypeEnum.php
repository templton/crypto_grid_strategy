<?php declare(strict_types=1);

namespace App\Enum;

enum OrderTypeEnum: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';
}
