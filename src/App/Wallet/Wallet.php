<?php declare(strict_types=1);

namespace App\Wallet;

use App\Utility\Math;

class Wallet
{
    public function __construct(private string $balance)
    {}

    public function add(string $amount): void
    {
        $this->balance = Math::sum($this->balance, $amount);
    }

    public function sub(string $amount): void
    {
        if (Math::compare($this->balance, $amount) === -1) {
            throw new \Exception('Not enough money. There is only ' . $this->balance);
        }

        $this->balance = Math::sub($this->balance, $amount);
    }

    /**
     * @return string
     */
    public function getBalance(): string
    {
        return $this->balance;
    }
}
