<?php declare(strict_types=1);

namespace App\Wallet;

use DateTime;

class WalletWithHistory
{
    private array $records;

    public function __construct(private Wallet $wallet)
    {}

    public function add(string $amount, DateTime $time): void
    {
        $this->wallet->add($amount);

        $this->records[] = [
            'time' => $time,
            'amount' => $this->wallet->getBalance(),
        ];
    }

    public function sub(string $amount, DateTime $time): void
    {
        $this->wallet->sub($amount);

        $this->records[] = [
            'time' => $time,
            'amount' => $this->wallet->getBalance(),
        ];
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function getBalance(): string
    {
        return $this->wallet->getBalance();
    }
}
