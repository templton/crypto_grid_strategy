<?php declare(strict_types=1);

namespace App\Order;

class OrderHistoryReport
{
    public function __construct(private OrderHistory $orderHistory)
    {}

    public function makeHtml(): string
    {
        $records = $this->orderHistory->getRecords();

        $data = $this->startTable();

        $data .= $this->headers();

        foreach ($records as $record) {
            $trContent = $this->td($record->getOrderUuid());
            $trContent .= $this->td($record->getOrderType()->value);
            $trContent .= $this->td($record->getTimeOpen()->format('Y-m-d H:i:s'));
            $trContent .= $this->td($record->getPrice());
            $trContent .= $this->td($record->getStatus()->value);
            $trContent .= $this->td(
                $record->getTimeClose()
                    ? $record->getTimeClose()->format('Y-m-d H:i:s')
                    : '',
            );
            $trContent .= $this->td($record->getNextSellOrderUui() ?? '');

            $data .= $this->tr($trContent);
        }

        $data .= $this->endTable();

        return $data;
    }

    private function headers(): string
    {
        $headers = [
            'Uuid',
            'Type',
            'Time open',
            'Price open',
            'Status',
            'Time close',
            'Next order uuid',
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
