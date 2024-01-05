<?php declare(strict_types=1);

namespace App\Enum;

enum OrderStatusEnum: string
{
    // Потенциальная откупка крипты, этот ордер может быть закрыть для экономии, когда цена от него далеко ушла
    case WAIT = 'WAIT';
    // Это реальный противоположный ордер, который должен по-любому отработать (Это только sell order)
    case OPEN = 'OPEN';
    // Ордер сработал sell/buy
    case CLOSE = 'CLOSE';
    case CANCEL = 'CANCEL';
}
