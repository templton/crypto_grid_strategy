<?php declare(strict_types=1);

namespace App\Order;

use App\Dto\CandleTdo;
use App\Enum\OrderStatusEnum;
use App\Enum\OrderTypeEnum;
use App\Enum\SymbolEnum;
use App\Utility\Math;
use DateTime;
use Exception;

class Order
{
    private float $marketFee = 0.1;
    private string $operationFee;
    private string $amountCrypto;
    private string $amountFiat;
    private string $orderUuid;
    private static int $ordersCount = 0;
    private DateTime $orderTime;
    private OrderStatusEnum $status;

    public function __construct(
        private SymbolEnum $symbol,
        private readonly OrderTypeEnum $type,
        private readonly string $price,
        private readonly CandleTdo $candle,
        string $amount = null,
    ){
        $this->calculateAmounts($amount);
        $this->genOrderUuid();

        $this->orderTime = $this->candle->getOpenTime();
    }

    public function getOrderTime(): \DateTime
    {
        return $this->orderTime;
    }

    public function getSymbol(): SymbolEnum
    {
        return $this->symbol;
    }

    public function getOrderUuid(): ?string
    {
        return $this->orderUuid;
    }

    private function genOrderUuid(): void
    {
        self::$ordersCount++;

        $this->orderUuid = (string)self::$ordersCount;
    }

    private function calculateAmounts(string $amount): void
    {
        if ($this->type === OrderTypeEnum::BUY) {
            $this->amountFiat = $amount;
            $this->amountCrypto = Math::div($this->amountFiat, $this->price);
            $this->operationFee = Math::getPercentPart($this->amountCrypto, $this->marketFee);
        } else {
            $this->amountCrypto = $amount;
            $this->amountFiat = Math::mul($this->amountCrypto, $this->price);
            $this->operationFee = Math::getPercentPart($this->amountFiat, $this->marketFee);
        }
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getStatus(): OrderStatusEnum
    {
        return $this->status;
    }

    public function setStatus(OrderStatusEnum $status): void
    {
        if ($status === OrderStatusEnum::CANCEL && $this->status === OrderStatusEnum::OPEN) {
            throw new Exception('Cannot set Cancel on Open order. Can only set Close');
        }

        $this->status = $status;
    }

    public function getType(): OrderTypeEnum
    {
        return $this->type;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getAmountFiat(): string
    {
        return $this->amountFiat;
    }

    public function getAmountCrypto(): string
    {
        return $this->amountCrypto;
    }

    public function getOperationFee(): string
    {
        return $this->operationFee;
    }

    public function getAmountFiatWithFee(): string
    {
        return Math::sub($this->amountFiat, $this->getFeeAmountFiat());
    }

    public function getAmountCryptoWithFee(): string
    {
        return Math::sub($this->amountCrypto, $this->getFeeAmountCrypto());
    }

    private function getFeeAmountFiat(): string
    {
        return Math::getPercentPart($this->amountFiat, $this->marketFee);
    }

    private function getFeeAmountCrypto(): string
    {
        return Math::getPercentPart($this->amountCrypto, $this->marketFee);
    }
}
