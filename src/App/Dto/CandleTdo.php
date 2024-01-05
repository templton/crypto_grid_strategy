<?php

namespace App\Dto;

use DateTime;

class CandleTdo
{
    private DateTime $closeTime;
    private DateTime $openTime;
    private string $open;
    private string $high;
    private string $low;
    private string $close;

    public static function createFromRaw(array $data): self
    {
        $candleDto = new self();

        $openTime = new DateTime();
        $openTime->setTimestamp(substr($data[0], 0, -3));

        $closeTime = new DateTime();
        $closeTime->setTimestamp(substr($data[6], 0, -3));

        $candleDto->openTime = $openTime;
        $candleDto->closeTime = $closeTime;
        $candleDto->open = $data[1];
        $candleDto->high = $data[2];
        $candleDto->low = $data[3];
        $candleDto->close = $data[4];

        return $candleDto;
    }

    public static function createMock(string $open, string $close, string $low, string $high): self
    {
        $candleDto = new self();

        $openTime = new DateTime();
        $closeTime = new DateTime();
        $candleDto->openTime = new DateTime();
        $candleDto->closeTime = new DateTime();
        $candleDto->open = $open;
        $candleDto->high = $high;
        $candleDto->low = $low;
        $candleDto->close = $close;

        return $candleDto;
    }

    public function getOpenTime(): DateTime
    {
        return $this->openTime;
    }

    public function getCloseTime(): DateTime
    {
        return $this->closeTime;
    }

    public function getOpen(): string
    {
        return $this->open;
    }

    public function getHigh(): string
    {
        return $this->high;
    }

    public function getLow(): string
    {
        return $this->low;
    }

    public function getClose(): string
    {
        return $this->close;
    }
}
