<?php

declare(strict_types=1);
require_once 'vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('logs/b24-api-client-debug.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));

$client = HttpClient::create(['http_version' => '2.0']);

try {
    $core = (new \Bitrix24\SDK\Core\CoreBuilder())
        ->withLogger($log)
        ->withWebhookUrl('https://b24-cdukpe.bitrix24.ru/rest/1/o1aiaw6ifekz1ryy/')
        ->build();


    //$res = $core->call('user.current');
    $res = $core->call('crm.deal.get',['ID'=>8]);
    var_dump($res->getResponseData()->getResult()->getResultData());
    $res = $core->call('crm.contact.get',['ID'=>2]);
    //var_dump($res->getResponseData()->getResult()->getResultData());
    var_dump($res->getResponseData()->getResult()->getResultData());
    $res = $core->call('crm.deal.productrows.get',['ID'=> 8]);
    var_dump($res->getResponseData()->getResult()->getResultData());
    $mass = $res->getResponseData()->getResult()->getResultData();

    print_r($mass);
    $arr = $mass[0];
    $pOld= $arr['PRICE'] ;
    $arr['DISCOUNT_SUM'] = '5';
    $arr['PRICE'] = $pOld- $arr['DISCOUNT_SUM'];
    $arr['PRICE_EXCLUSIVE'] = $pOld- $arr['DISCOUNT_SUM'];
    $arr['PRICE_NETTO'] = $pOld- $arr['DISCOUNT_SUM'];
    $arr['PRICE_BRUTTO'] = $pOld- $arr['DISCOUNT_SUM'];
    $arr['PRICE_ACCOUNT'] = $pOld- $arr['DISCOUNT_SUM'];
    print_r($arr);
    $res = $core->call('crm.deal.productrows.set',['ID'=> 8, 'ROWS'=> [$arr]]);
    var_dump($res->getResponseData()->getResult()->getResultData());


    $res = $core->call('crm.deal.productrows.get',['ID'=> 8]);
    var_dump($res->getResponseData()->getResult()->getResultData());
   // var_dump($res->getResponseData()->getResult()->getResultData()['ID']);
   // var_dump($res->getResponseData()->getResult()->getResultData()['EMAIL']);*/
} catch (\Throwable $exception) {
    print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
    print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
    print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
}