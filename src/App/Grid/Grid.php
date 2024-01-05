<?php declare(strict_types=1);

namespace App\Grid;

use App\Dto\CandleTdo;
use App\Utility\Math;

/**
 * Границы сделать через цепочку. Каждый элемент содержит ссылку на предыдущий и следующий
 */

class Grid
{
    public const MODE_PERCENT = 'percent';
    /**
     * @var BorderOrders[] array
     */
    private array $borders;

    private int $countGridSteps = 500;

    public function __construct(private string $initPrice, private int $gridStepPercent, private string $mode){}

    public function init(): void
    {
        switch ($this->mode) {
            case self::MODE_PERCENT:
                $this->makePercentBorders();
                return;
        }

        throw new \Exception('Unknown grid mode');
    }

    // Условно считаем, что свеча двигалась только в одном направлении: или бык, или медведь
    // Ищем границу. Смотрим от candle->low и дальше в сторону high. Берем первый попавшийся border
    // Если цена за 1 минуту прошла несколько границ, то возвращаем все границы
    // Borders будут расположены с направлением их цен в соответствии с направлением свечи (медвежья/бычья)
    /**
     * @return BorderOrders[]
     */
    public function findBorder(CandleTdo $candle): array
    {
        $isBull = Math::compare($candle->getOpen(), $candle->getClose()) === -1;
        $low = $candle->getLow();
        $high = $candle->getHigh();

        $borders = array_filter(
            $isBull ? $this->borders : array_reverse($this->borders),
            fn($border) => $this->isPriceOnCandle($border->getPrice(), $low, $high),
        );

        return array_values($borders);
    }

    public function findBorderDyIndex(int $index): BorderOrders
    {
        return $this->borders[$index];
    }

    private function isPriceOnCandle(string $price, string $low, string $high): bool
    {
        return Math::compare($low, $price) === -1 && Math::compare($price, $high) === -1;
    }

    private function makePercentBorders(): void
    {
        $borderPrices = [$this->initPrice];

        $count = 0;
        while ($count < $this->countGridSteps) {
            $borderPrices[] = $this->getPriceOnGridStep($count, false);
            $borderPrices[] = $this->getPriceOnGridStep($count);
            $count++;
        }

        sort($borderPrices);

        $this->borders = array_map(fn($price) => new BorderOrders($price), $borderPrices);

        $index = 0;
        foreach ($this->borders as $border) {
            $border->setIndex($index);
            $index++;
        }
    }

    private function getPriceOnGridStep($count, $isUpDirection = true): string
    {
        $percent = ($this->countGridSteps - $count) * $this->gridStepPercent;

        return $isUpDirection
            ? Math::addPercent($this->initPrice, $percent)
            : Math::subPercent($this->initPrice, $percent);
    }

    public function getBorders(): array
    {
        return $this->borders;
    }
}
