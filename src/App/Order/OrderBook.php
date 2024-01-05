<?php //declare(strict_types=1);
//
//namespace App\Order;
//
//class OrderBook
//{
//    private int $maxOrderNum = 1;
//    /**
//     * @var OrderBookRecord[]
//     */
//    private array $records = [];
//
//    public function add(Order $order, int $gridIndex): void
//    {
//        $orderNum = $this->genOrderNum();
//        $order->setOrderNum($orderNum);
//
//        $record = new OrderBookRecord($gridIndex);
//        $record->setOpen($order);
//
//        $this->records[] = $record;
//    }
//
//    public function findRecord(int $girdIndex): OrderBookRecord
//    {
//        return array_filter(
//            $this->records,
//            fn($rec) => $rec->getGridIndex() === $girdIndex,
//        )[0];
//    }
//
//    public function close(Order $order): int
//    {}
//
//    private function genOrderNum(): int
//    {
//        $this->maxOrderNum++;
//
//        return $this->maxOrderNum;
//    }
//
//    /**
//     * @return OrderBookRecord[]
//     */
//    public function getRecords(): array
//    {
//        return $this->records;
//    }
//}
