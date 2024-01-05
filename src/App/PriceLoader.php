<?php declare(strict_types=1);

/**
 * [
 * 0:  1499040000000,      // Open time
 * 1:  "0.01634790",       // Open
 * 2:  "0.80000000",       // High
 * 3:  "0.01575800",       // Low
 * 4:  "0.01577100",       // Close
 * 5:  "148976.11427815",  // Volume
 * 6:  1499644799999,      // Close time
 * 7:  "2434.19055334",    // Quote asset volume
 * 8:  308,                // Number of trades
 * 9:  "1756.87402397",    // Taker buy base asset volume
 * 10: "28.46694368",      // Taker buy quote asset volume
 * 11: "17928899.62484339" // Ignore.
 * ]
 */

namespace App;

use App\Dto\CandleTdo;
use Cassandra\Date;
use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Psr7\Response;

class PriceLoader
{
//    public const BTCUSDT = 'BTCUSDT';
//    public const ALL_PAIRS = [
//        self::BTCUSDT, 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT', 'USDCUSDT', 'ADAUSDT', 'AVAXUSDT', 'DOGEUSDT', 'DOTUSDT',
//        'TRXUSDT', 'MATICUSDT', 'LINKUSDT', 'WBTCUSDT', 'ICPUSDT', 'SHIBUSDT', 'LTCUSDT', 'BCHUSDT', 'UNIUSDT', 'ATOMUSDT',
//        'NEARUSDT', 'XLMUSDT', 'OPUSDT', 'FILUSDT', 'INJUSDT', 'APTUSDT', 'HBARUSDT', 'ETCUSDT', 'XMRUSDT', 'IMXUSDT',
//        'LDOUSDT', 'ARBUSDT', 'VETUSDT', 'TUSDUSDT', 'STXUSDT', 'TIAUSDT', 'FDUSDUSDT', 'RUNEUSDT', 'SEIUSDT', 'WBETHUSDT',
//        'GRTUSDT', 'RNDRUSDT', 'ALGOUSDT', 'MKRUSDT', 'ORDIUSDT', 'QNTUSDT', 'EGLDUSDT', 'AAVEUSDT', '1000SATSUSDT',
//        'MINAUSDT', 'FLOWUSDT', 'FTMUSDT', 'THETAUSDT', 'SANDUSDT', 'SNXUSDT', 'AXSUSDT', 'XTZUSDT', 'ASTRUSDT', 'FTTUSDT',
//        'SUIUSDT', 'PEOPLEUSDT', 'ENSUSDT', 'CYBERUSDT', 'POWRUSDT', 'PERPUSDT', 'BLURUSDT', 'MOVRUSDT', 'YGGUSDT', 'WLDUSDT',
//        'BONKUSDT', 'BAKEUSDT', 'CAKEUSDT', 'CHRUSDT', 'GMTUSDT', 'RDNTUSDT', 'TRXUSDT', 'GALAUSDT', 'SSVUSDT', 'MEMEUSDT',
//        'ACEUSDT', 'PEPEUSDT', 'NFPUSDT'
//    ];
    public const TIMEFRAME_1D = '1d';
    public const TIMEFRAME_1M = '1m';
    public const TIMEFRAME_1H = '1h';
    public const TIMEFRAME_15M = '15m';

    private Client $client;
    private string $apiUrl = 'https://api.binance.com/';
    private const METHOD_KLINES = 'klines';
    private const API_VERSION = '/api/v1';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
        ]);
    }

    /**
     * @return CandleTdo[]
     */
    public function loadData(string $symbol, string $interval, DateTime $startTime, DateTime $endTime = null): array
    {
        $result = $this->klines($symbol, $interval, $startTime);

        if (!$endTime) {
            $endTime = new DateTime();
        }

        $resultTime = clone end($result)->getCloseTime();

        $countRequest = 1;

        while ($resultTime->getTimestamp() < $endTime->getTimestamp()) {
            $result = array_merge($result, $this->klines($symbol, $interval, $resultTime));

            $resultTime = clone end($result)->getCloseTime();

            $countRequest++;
        }

//        $this->validateDataSet($result, $interval);

        $statistic = [
            'countRequests' => $countRequest,
            'countCandles' => count($result),
        ];

//        echo "<pre>";print_r($statistic);echo "</pre>";die;

        return $result;
    }

    private function validateDataSet(array $result, string $initInterval): void
    {
        $currentInterval = $this->createDateInterval($initInterval);
        for($i=0; $i < count($result) - 1; $i++) {
            $date1 = $result[$i]->getCloseTime();
            $date2 = $result[$i+1]->getCloseTime();
            $diffInterval = $date1->diff($date2);

            if ($this->compareDateIntervals($diffInterval, $currentInterval, '!=')) {
                echo '<b>Wrong interval difference!</b><br><br>';

                echo '
                    <table cellpadding="10">
                        <tr>
                            <th>Current interval</th>
                            <th>Calculated interval</th>
                            <th>Candle 1</th>
                            <th>Candle 2</th>
                        </tr>
                        <tr>
                            <td>' . '<pre>' . print_r($currentInterval, true) . '</pre>' . '</td>
                            <td>' . '<pre>' . print_r($diffInterval, true) . '</pre>' . '</td>
                            <td>' . '<pre>' . print_r($result[$i], true) . '</pre>' . '</td>
                            <td>' . '<pre>' . print_r($result[$i+1], true) . '</pre>' . '</td>
                        </tr>
                    </table>
                ';

                die();
            }
        }
    }

    private function compareDateIntervals(DateInterval $dateInterval1, DateInterval $dateInterval2, string $operator): bool
    {
        $date1 = new DateTime();
        $date2 = clone $date1;

        $date1->add($dateInterval1);
        $date2->add($dateInterval2);

        switch ($operator) {
            case '>':
                return $date1->getTimestamp() > $date2->getTimestamp();
            case '>=':
                return $date1->getTimestamp() >= $date2->getTimestamp();
            case '<':
                return $date1->getTimestamp() < $date2->getTimestamp();
            case '<=':
                return $date1->getTimestamp() <= $date2->getTimestamp();
            case '=':
                return $date1->getTimestamp() === $date2->getTimestamp();
            case '!=':
                return $date1->getTimestamp() !== $date2->getTimestamp();
        }

        throw new Exception('Invalid operator value');
    }

    private function createDateInterval($interval): DateInterval
    {
        $prefix = strpos($interval, 'd') || strpos($interval, 'w')
            ? 'P'
            : 'PT';

        $value = substr($interval, 0, -1);
        $period = strtoupper(substr($interval, -1, 1));

        return new DateInterval("{$prefix}{$value}{$period}");
    }

    /**
     * @return CandleTdo[] array
     */
    private function klines(
        string   $symbol,
        string   $interval,
        DateTime $startTime,
        int      $limit = 1500
    ): array
    {
        $params = ['query' => [
            'symbol' => $symbol,
            'interval' => $interval,
            'startTime' => $startTime->getTimestamp() . '000',
            'limit' => $limit,
        ]];

        $cacheKey = implode('', $params['query']);
        $fileName = __DIR__ . '/../cache/' . $cacheKey;

        if (file_exists($fileName)) {
            return unserialize(file_get_contents($fileName));
        }

        $response = $this->createRequest(self::METHOD_KLINES, [
            'query' => [
                'symbol' => $symbol,
                'interval' => $interval,
                'startTime' => $startTime->getTimestamp() . '000',
                'limit' => $limit,
            ]
        ]);

        $data = json_decode($response->getBody()->getContents());

        $data = array_map(fn($item) => CandleTdo::createFromRaw($item), $data);

        file_put_contents($fileName, serialize($data));

        return $data;
    }

    private function createRequest(string $methodName, array $params = [], string $methodType = 'GET'): Response
    {
        $uri = self::API_VERSION . '/' . $methodName;

        return $this->client->request($methodType, $uri, $params);
    }
}
