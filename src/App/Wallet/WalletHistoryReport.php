<?php declare(strict_types=1);

namespace App\Wallet;

class WalletHistoryReport
{
    public function __construct(private readonly WalletWithHistory $walletWithHistory)
    {}

    public function getHtmlReport(): string
    {
        $records = $this->walletWithHistory->getRecords();

        $data = $this->startTable();

        $data .= $this->headers();

        $i = 1;
        foreach ($records as $record) {
            $trContent = $this->td((string)$i);
            $trContent .= $this->td($record['time']->format('Y-m-d H:i:s'));
            $trContent .= $this->td($record['amount']);

            $data .= $this->tr($trContent);

            $i++;
        }

        $data .= $this->endTable();

        return $data;
    }

    private function headers(): string
    {
        $headers = [
            '#',
            'time',
            'balance',
        ];

        $trContent = '';
        foreach ($headers as $item) {
            $trContent .= $this->td($item);
        }

        return $this->tr($trContent);
    }

    private function startTable(): string
    {
        return '<table border="1" cellpadding="5">';
    }

    private function endTable(): string
    {
        return '</table>';
    }

    private function tr(string $content): string
    {
        return '<tr>' . $content . '</tr>';
    }

    private function td(string $content): string
    {
        return '<td>' . $content . '</td>';
    }
}
