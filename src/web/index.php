<?php

require '../vendor/autoload.php';

use App\Enum\SymbolEnum;
use App\Enum\TimeframeEnum;
use App\PriceLoader;
use App\Stac;
use App\Tester\Tester;
use App\Tester\TesterParamsDto;

function makeTradeTest()
{
    $gridPercent = 15; // Шаг сетки в процентах
    $walletBalance = '100'; // Стартовый баланс кошелька в USDT
    $orderAmount = '10'; // Фиксированная сумма одного ордера на покупку
    $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2023-01-01 00:00:00');
    $endDate = new DateTime();


    $tester = new Tester(new TesterParamsDto(
        SymbolEnum::SSVUSDT,
        $gridPercent,
        TimeframeEnum::M1,
        $orderAmount,
        $walletBalance,
        $startDate,
        $endDate
    ));

    $tester->test();

    echo $tester->getHtmlHistoryReport();
}

function printPairsVolatility()
{
    $importer = new PriceLoader();

    $symbols = SymbolEnum::BTCUSDT;
    $symbols = [
        'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT', 'USDCUSDT', 'ADAUSDT', 'AVAXUSDT', 'DOGEUSDT', 'DOTUSDT',
        'TRXUSDT', 'MATICUSDT', 'LINKUSDT', 'WBTCUSDT', 'ICPUSDT', 'SHIBUSDT', 'LTCUSDT', 'BCHUSDT', 'UNIUSDT', 'ATOMUSDT',
        'NEARUSDT', 'XLMUSDT', 'OPUSDT', 'FILUSDT', 'INJUSDT', 'APTUSDT', 'HBARUSDT', 'ETCUSDT', 'XMRUSDT', 'IMXUSDT',
        'LDOUSDT', 'ARBUSDT', 'VETUSDT', 'TUSDUSDT', 'STXUSDT', 'TIAUSDT', 'FDUSDUSDT', 'RUNEUSDT', 'SEIUSDT', 'WBETHUSDT',
        'GRTUSDT', 'RNDRUSDT', 'ALGOUSDT', 'MKRUSDT', 'ORDIUSDT', 'QNTUSDT', 'EGLDUSDT', 'AAVEUSDT', '1000SATSUSDT',
        'MINAUSDT', 'FLOWUSDT', 'FTMUSDT', 'THETAUSDT', 'SANDUSDT', 'SNXUSDT', 'AXSUSDT', 'XTZUSDT', 'ASTRUSDT', 'FTTUSDT',
        'SUIUSDT', 'PEOPLEUSDT', 'ENSUSDT', 'CYBERUSDT', 'POWRUSDT', 'PERPUSDT', 'BLURUSDT', 'MOVRUSDT', 'YGGUSDT', 'WLDUSDT',
        'BONKUSDT', 'BAKEUSDT', 'CAKEUSDT', 'CHRUSDT', 'GMTUSDT', 'RDNTUSDT', 'TRXUSDT', 'GALAUSDT', 'SSVUSDT', 'MEMEUSDT',
        'ACEUSDT', 'PEPEUSDT', 'NFPUSDT'
    ];

    $startTime = DateTime::createFromFormat('Y-m-d H:i:s', '2023-01-01 00:00:00');
    $timeframe = TimeframeEnum::D1->value;

    echo '<table border="1" cellpadding="5">';

    echo '
    <tr>
        <td>Coin</td>
        <td>0</td>
        <td>5</td>
        <td>10</td>
        <td>15</td>
        <td>20</td>
        <td>25</td>
        <td>30</td>
        <td>35</td>
        <td>40</td>
        <td>45</td>
        <td>50</td>
        <td>55</td>
        <td>60</td>
        <td>65</td>
        <td>days</td>
    </tr>
';

    foreach ($symbols as $symbol) {
        $data = $importer->loadData(
            $symbol,
            $timeframe,
            $startTime,
        );

        $stac = new Stac($data);
        $volatility = $stac->getVolatility();

        $sum = 0;
        foreach ($volatility as $v) {
            $sum = $sum + $v;
        }

        echo '
        <tr>
            <td>' . $symbol . '</td>
            <td>' . ($volatility[0] ?? '') . '</td>
            <td>' . ($volatility[5] ?? '') . '</td>
            <td>' . ($volatility[10] ?? '') . '</td>
            <td>' . ($volatility[15] ?? '') . '</td>
            <td>' . ($volatility[20] ?? '') . '</td>
            <td>' . ($volatility[25] ?? '') . '</td>
            <td>' . ($volatility[30] ?? '') . '</td>
            <td>' . ($volatility[35] ?? '') . '</td>
            <td>' . ($volatility[40] ?? '') . '</td>
            <td>' . ($volatility[45] ?? '') . '</td>
            <td>' . ($volatility[50] ?? '') . '</td>
            <td>' . ($volatility[55] ?? '') . '</td>
            <td>' . ($volatility[60] ?? '') . '</td>
            <td>' . ($volatility[65] ?? '') . '</td>
            <td>' . ($sum) . '</td>
    </tr>
    ';
    }

    echo '</table>';
}

//makeTradeTest();

printPairsVolatility();
