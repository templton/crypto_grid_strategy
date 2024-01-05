<?php declare(strict_types=1);

namespace App;

use App\Dto\CandleTdo;
use App\Utility\ArrayHelper;
use App\Utility\Math;

class Stac
{
    /**
     * @var CandleTdo[]
     */
    private array $data;

    /**
     * @param CandleTdo[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getVolatility(): array
    {
        $volatilityByDays = [];
        $volatilitySummaryPercent = [];

        foreach ($this->data as $item) {
            $deltaPercent = Math::deltaPercent($item->getLow(), $item->getHigh());
            $index = intdiv(intval($deltaPercent), 5) * 5;

            if (!isset($volatilitySummaryPercent[$index])) {
                $volatilitySummaryPercent[$index] = 0;
                $volatilityByDays[$index] = [];
            }

            $volatilityByDays[$index][] = $item->getOpenTime()->format('Y-m-d');
            $volatilitySummaryPercent[$index]++;
        }

        $volatilitySummaryPercent = ArrayHelper::sortKeys($volatilitySummaryPercent);
        $volatilityByDays = ArrayHelper::sortKeys($volatilityByDays);

//        echo "<pre>";print_r($this->data);echo "</pre>";die;
//        echo "<pre>";print_r($volatilityByDays);echo "</pre>";die;

        return $volatilitySummaryPercent;
    }
}
