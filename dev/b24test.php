<?php

declare(strict_types=1);
require_once 'vendor/autoload.php';
echo 1;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;

echo 2;
$log = new Logger('name');
$log->pushHandler(new StreamHandler('logs/b24-api-client-debug.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));

$client = HttpClient::create(['http_version' => '2.0']);

try {
    echo 1;
    $core = (new \Bitrix24\SDK\Core\CoreBuilder())
        ->withLogger($log)
        ->withWebhookUrl('https://b24-cdukpe.bitrix24.ru/rest/1/o1aiaw6ifekz1ryy/')
        ->build();
    /*$res = $core->call('crm.deal.productrows.get',['ID'=>2]);
    var_dump($res->getResponseData()->getResult()->getResultData());*/
    $res = $core->call('user.current');
    var_dump($res->getResponseData()->getResult()->getResultData());
    var_dump($res->getResponseData()->getResult()->getResultData()['ID']);

    var_dump($res->getResponseData()->getResult()->getResultData()['EMAIL']);
    $res = $core->call('crm.deal.get',['ID'=>54]);
    $arrayOrderData = $res->getResponseData()->getResult()->getResultData();
    print_r($arrayOrderData);

    /*$res = $core->call('crm.deal.get',['ID'=>30]);
    var_dump($res->getResponseData()->getResult()->getResultData());
    $res = $core->call('crm.contact.get',['ID'=>2]);
    var_dump($res->getResponseData()->getResult()->getResultData());
    $res = $core->call('crm.deal.productrows.get',['ID'=> 30]);
    var_dump($res->getResponseData()->getResult()->getResultData());

    $mass = $res->getResponseData()->getResult()->getResultData();

    print_r($mass);*/
} catch (\Throwable $exception) {
    print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
    print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
    print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
}
