<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once("HttpInterface/ParseNewOrder.php");

use HttpInterface\ParseNewOrder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


$log = new Logger('name');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
$log->pushProcessor(new \Monolog\Processor\WebProcessor());
$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

print('<pre>');
var_dump('hello 1111111');
error_reporting(E_ALL);
ini_set("error_reporting","E_ALL");
ini_set("error_log", "log.txt"); // расположение лог-файла ошибок
ini_set("log_errors", "true"); // включить лог ошибок
error_log( "Hello, errors!" ); // записать в лог-файл значение/строку
$order = new ParseNewOrder();
$order->makeOrderData();

$log->debug(
    'req',
    [
        'req' => $order->makeOrderData(),
    ]
);
