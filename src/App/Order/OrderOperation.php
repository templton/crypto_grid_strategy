<?php declare(strict_types=1);

namespace App\Order;

use App\Enum\OrderStatusEnum;
use App\Enum\OrderTypeEnum;
use App\Enum\SymbolEnum;
use App\Order\Order;
use DateTime;

class OrderOperation
{
    private string $orderUuid;
    private OrderTypeEnum $orderType;
    private SymbolEnum $symbol;
    private string $price;
//    private string $priceClose;
    private DateTime $timeOpen;
    private ?DateTime $timeClose = null;
    private string $amountFiat;
//    private string $amountFiatClose;
    private string $amountCrypto;
//    private string $amountCryptoClose;
    private string $fee;
//    private string $feeClose;
    private OrderStatusEnum $status;

    private ?string $nextSellOrderUui = null;

    public function __construct(Order $order)
    {
        $this->orderUuid = $order->getOrderUuid();
        $this->orderType = $order->getType();
        $this->symbol = $order->getSymbol();
        $this->price = $order->getPrice();
        $this->timeOpen = $order->getOrderTime();
        $this->amountFiat = $order->getAmountFiat();
        $this->amountCrypto = $order->getAmountCrypto();
        $this->fee = $order->getOperationFee();
        $this->status = $order->getStatus();
    }

//    public function closeBuyOrder(Order $order): void
//    {
//        $this->timeClose = $order->getOrderTime();
//        $this->priceClose = $order->getPrice();
//        $this->amountFiatClose = $order->getAmountFiat();
//        $this->amountCryptoClose = $order->getAmountCrypto();
//        $this->feeClose = $order->getOperationFee();
//    }

    public function close(
        DateTime        $time,
        OrderStatusEnum $status,
        ?string         $nextSellOrderUui,
    ): void {
        $this->timeClose = $time;
        $this->status = $status;
        $this->nextSellOrderUui = $nextSellOrderUui;
    }

    /**
     * @return OrderStatusEnum
     */
    public function getStatus(): OrderStatusEnum
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getNextSellOrderUui(): ?string
    {
        return $this->nextSellOrderUui;
    }

    /**
     * @return string
     */
    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    /**
     * @return OrderTypeEnum
     */
    public function getOrderType(): OrderTypeEnum
    {
        return $this->orderType;
    }

    /**
     * @return SymbolEnum
     */
    public function getSymbol(): SymbolEnum
    {
        return $this->symbol;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceClose(): string
    {
        return $this->priceClose;
    }

    /**
     * @return DateTime
     */
    public function getTimeOpen(): DateTime
    {
        return $this->timeOpen;
    }

    /**
     * @return ?DateTime
     */
    public function getTimeClose(): ?DateTime
    {
        return $this->timeClose;
    }

    /**
     * @return string
     */
    public function getAmountFiat(): string
    {
        return $this->amountFiat;
    }

    /**
     * @return string
     */
    public function getAmountFiatClose(): string
    {
        return $this->amountFiatClose;
    }

    /**
     * @return string
     */
    public function getAmountCrypto(): string
    {
        return $this->amountCrypto;
    }

    /**
     * @return string
     */
    public function getAmountCryptoClose(): string
    {
        return $this->amountCryptoClose;
    }

    /**
     * @return string
     */
    public function getFee(): string
    {
        return $this->fee;
    }

    /**
     * @return string
     */
    public function getFeeClose(): string
    {
        return $this->feeClose;
    }
}
