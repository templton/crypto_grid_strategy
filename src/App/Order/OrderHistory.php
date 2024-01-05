<?php declare(strict_types=1);

namespace App\Order;

use App\Enum\OrderStatusEnum;
use DateTime;

class OrderHistory
{
    /**
     * @var OrderOperation[]
     */
    private array $records;

    public function add(Order $order): void
    {
        $this->records[] = new OrderOperation($order);
    }

//    public function closeBuy(string $orderUui, Order $order): void
//    {
//        $this->findRecordByOrderUuid($orderUui)->closeBuyOrder($order);
//    }

    public function close(
        string          $orderUui,
        DateTime        $time,
        OrderStatusEnum $status,
        ?string         $nextSellOrderUui = null,
    ): void {
        $this->findRecordByOrderUuid($orderUui)->close($time, $status, $nextSellOrderUui);
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    private function findRecordByOrderUuid(string $orderUui): OrderOperation
    {
        $record = array_filter($this->records, fn($rec) => $rec->getOrderUuid() === $orderUui);

        return end($record);
    }
}
