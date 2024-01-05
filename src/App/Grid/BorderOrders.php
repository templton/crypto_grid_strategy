<?php declare(strict_types=1);

namespace App\Grid;

use App\Order\Order;

class BorderOrders
{
    private ?Order $sellOrder = null;
    private ?Order $buyOrder = null;

    private int $index;

    public function __construct(private readonly string $price){}

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getSellOrder(): ?Order
    {
        return $this->sellOrder;
    }

    public function setSellOrder(?Order $sellOrder): void
    {
        $this->sellOrder = $sellOrder;
    }

    public function getBuyOrder(): ?Order
    {
        return $this->buyOrder;
    }

    public function setBuyOrder(?Order $buyOrder): void
    {
        $this->buyOrder = $buyOrder;
    }

    public function getPrice(): string
    {
        return $this->price;
    }
}
