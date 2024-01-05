<?php declare(strict_types=1);

namespace App\Tester;

use App\Dto\CandleTdo;
use App\Enum\OrderStatusEnum;
use App\Enum\OrderTypeEnum;
use App\Grid\BorderOrders;
use App\Grid\Grid;
use App\Order\Order;
use App\Order\OrderHistory;
use App\Order\OrderHistoryReport;
use App\PriceLoader;
use App\Wallet\Wallet;
use App\Wallet\WalletHistoryReport;
use App\Wallet\WalletWithHistory;
use Exception;

/**
 * Отсюда вынести все операции, кроме бизнес логики самой стратегии: размещение ордера и другие
 */

class Tester
{
    private Grid $grid;
    /**
     * @var CandleTdo[]
     */
    private array $candles;
    private WalletWithHistory $wallet;

    private OrderHistory $orderHistory;

    public function __construct(private readonly TesterParamsDto $params)
    {
        $this->initMarket();
        $this->initGrid();
        $this->initWallet();
        $this->orderHistory = new OrderHistory();
    }

    private function initMarket(): void
    {
        $loader = new PriceLoader();

        $this->candles = $loader->loadData(
            $this->params->getSymbol()->value,
            $this->params->getTimeframe()->value,
            $this->params->getStartDate(),
        );
    }

    private function initGrid(): void
    {
        $firstCandle = $this->candles[0];
        $this->grid = new Grid(
            $firstCandle->getLow(),
            $this->params->getGridPercentStep(),
            Grid::MODE_PERCENT,
        );

        $this->grid->init();
    }

    private function initWallet(): void
    {
        $this->wallet = new WalletWithHistory(
            new Wallet($this->params->getWalletBalance()),
        );
    }

    public function test(): void
    {
        array_walk($this->candles, [$this, 'processWorkCandle']);
    }

    private function processWorkCandle(CandleTdo $candle): void
    {
        $borderItems = $this->grid->findBorder($candle);

        if (!$borderItems) {
            return;
        }

        $marketBorder = $borderItems[0];
        $index = $marketBorder->getIndex();

        // TODO На самом деле надо передать только маркет, а остальные должны быть доступны по цепочке связей
        $borderDown1 = $this->grid->findBorderDyIndex($index - 1);
        $borderDown2 = $this->grid->findBorderDyIndex($index - 2);
        $borderUp1 = $this->grid->findBorderDyIndex($index + 1);
        $borderUp2 = $this->grid->findBorderDyIndex($index + 2);

//        echo "<pre>";print_r($this->grid->getBorders());echo "</pre>";die;

//        echo "<pre>";print_r($marketBorder);echo "</pre>";
//        echo "<pre>";print_r($borderDown1);echo "</pre>";
//        echo "<pre>";print_r($borderDown2);echo "</pre>";
//        echo "<pre>";print_r($borderUp1);echo "</pre>";
//        echo "<pre>";print_r($borderUp2);echo "</pre>";die;

        // По текущему маркету закрываем ордера: buy(wait)/sell(open)
        $this->processOrdersInMarket($marketBorder, $borderUp1, $candle);
        // От текущего маркета выставляем wait на покупку с двух сторон сетки,
        // если еще не выставлен и если нет противоположного sell(open)
        $this->placeWaitOrders($marketBorder, $borderDown1, $borderUp1, $borderUp2, $candle);
        // Отменить buy(wait) ордера на сетке +2 границы, что освободить средства
        $this->cancelWaitOrders($borderDown2, $borderUp2, $candle);
    }

    private function processOrdersInMarket(BorderOrders $marketBorder, BorderOrders $borderUp1, CandleTdo $candle): void
    {
        $sellOrder = $marketBorder->getSellOrder();

        if ($sellOrder) {
            $sellOrder->setStatus(OrderStatusEnum::CLOSE);

            $this->orderHistory->close(
                $sellOrder->getOrderUuid(),
                $candle->getOpenTime(),
                OrderStatusEnum::CLOSE,
            );

            $this->wallet->add($sellOrder->getAmountFiatWithFee(), $candle->getOpenTime());

            $marketBorder->setSellOrder(null);
        }

        $buyOrder = $marketBorder->getBuyOrder();

        if ($buyOrder) {
            $buyOrder->setStatus(OrderStatusEnum::CLOSE);
            $reverseSellOrder = $this->makeSellOrder($borderUp1->getPrice(), $candle, $buyOrder);

            $this->orderHistory->close(
                $buyOrder->getOrderUuid(),
                $candle->getOpenTime(),
                OrderStatusEnum::CLOSE,
                $reverseSellOrder->getOrderUuid(),
            );

            $marketBorder->setBuyOrder(null);

            if ($borderUp1->getSellOrder()) {
                throw new Exception('There is already exists Sell order on border!');
            }

            $borderUp1->setSellOrder($reverseSellOrder);
        }
    }

    private function placeWaitOrders(
        BorderOrders $marketBorder,
        BorderOrders $borderDown1,
        BorderOrders $borderUp1,
        BorderOrders $borderUp2,
        CandleTdo $candle,
    ): void {
        if (!$borderDown1->getBuyOrder() && !$marketBorder->getSellOrder()) {
            $borderDown1->setBuyOrder(
                $this->makeBuyOrder($borderDown1->getPrice(), $candle),
            );
        }

        if (!$borderUp1->getBuyOrder() && !$borderUp2->getSellOrder()) {
            $borderUp1->setBuyOrder(
                $this->makeBuyOrder($borderUp1->getPrice(), $candle),
            );
        }
    }

    private function cancelWaitOrders(BorderOrders $borderDown2, BorderOrders $borderUp2, CandleTdo $candle): void
    {
        $orderDown = $borderDown2->getBuyOrder();

        if ($orderDown) {
            $this->cancelOrder($orderDown, $candle);
            $this->orderHistory->close($orderDown->getOrderUuid(), $candle->getOpenTime(), OrderStatusEnum::CANCEL);
            $borderDown2->setBuyOrder(null);
        }

        $orderUp = $borderUp2->getBuyOrder();

        if ($orderUp) {
            $this->cancelOrder($orderUp, $candle);
            $this->orderHistory->close($orderUp->getOrderUuid(), $candle->getOpenTime(), OrderStatusEnum::CANCEL);
            $borderUp2->setBuyOrder(null);
        }
    }

    private function makeBuyOrder(string $price, CandleTdo $candle): Order
    {
        $amount = $this->params->getOrderAmount();

        $this->wallet->sub($amount, $candle->getOpenTime());

        $buyOrder = $this->createBuyOrder($price, $candle, $amount);
        $buyOrder->setStatus(OrderStatusEnum::WAIT);

        $this->orderHistory->add($buyOrder);

        return $buyOrder;
    }

    private function makeSellOrder(string $price, CandleTdo $candle, Order $buyOrder): Order
    {
        $sellOrder = $this->createSellOrder($price, $candle, $buyOrder->getAmountCryptoWithFee());
        $sellOrder->setStatus(OrderStatusEnum::OPEN);

//        $this->wallet->add($sellOrder->getAmountFiatWithFee(), $candle->getOpenTime());

        $this->orderHistory->add($sellOrder);

        return $sellOrder;
    }

    private function cancelOrder(Order $order, CandleTdo $candle): void
    {
        if ($order->getStatus() === OrderStatusEnum::OPEN) {
            throw new Exception('Cannot set Cancel on Open order');
        }

        $this->wallet->add($order->getAmountFiat(), $candle->getOpenTime());
    }

    private function createBuyOrder(string $price, CandleTdo $candle, string $amount): Order
    {
        return new Order(
            $this->params->getSymbol(),
            OrderTypeEnum::BUY,
            $price,
            $candle,
            $amount,
        );
    }

    private function createSellOrder(string $price, CandleTdo $candle, string $amount): Order
    {
        return new Order(
            $this->params->getSymbol(),
            OrderTypeEnum::SELL,
            $price,
            $candle,
            $amount,
        );
    }

    public function getHtmlHistoryReport(): string
    {
        $report = new OrderHistoryReport($this->orderHistory);
        $html = $report->makeHtml();

        $html .= "<br><br>";

        $walletReport = new WalletHistoryReport($this->wallet);
        $html .= $walletReport->getHtmlReport();

        $html .= "<br><br>";

        $html .= "Count open orders = " . count($this->getPlacedOrders());

        $html .= "<br><br>";

        $html .= 'Balance: ' . $this->wallet->getBalance();

        return $html;
    }

    /**
     * @return Order[]
     */
    private function getPlacedOrders(): array
    {
        $orders = [];
        foreach ($this->grid->getBorders() as $border) {
            if ($border->getSellOrder()) {
                $orders[] = $border->getSellOrder();
            }
            if ($border->getBuyOrder()) {
                $orders[] = $border->getBuyOrder();
            }
        }

        return $orders;
    }
}
