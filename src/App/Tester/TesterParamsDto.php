<?php declare(strict_types=1);

namespace App\Tester;

use App\Dto\CandleTdo;
use App\Enum\SymbolEnum;
use App\Enum\TimeframeEnum;
use DateTime;

class TesterParamsDto
{
    private const DEFAULT_TIMEFRAME = '1h';

    public function __construct(
        private SymbolEnum $symbol,
        private int $gridPercentStep, // Шаг сетки в процентах
        private TimeframeEnum $timeframe,
        private string $orderAmount, // Сумма одного ордера в USDT
        private string $walletBalance,
        private DateTime $startDate,
        private DateTime $endDate,
    ){}

    /**
     * @return string
     */
    public function getWalletBalance(): string
    {
        return $this->walletBalance;
    }

    public function getOrderAmount(): string
    {
        return $this->orderAmount;
    }

    public function getSymbol(): SymbolEnum
    {
        return $this->symbol;
    }

    public function getGridPercentStep(): int
    {
        return $this->gridPercentStep;
    }

    public function getTimeframe(): TimeframeEnum
    {
        return $this->timeframe;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }
}
