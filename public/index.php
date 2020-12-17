<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
include("/etc/php.d/php-scripts.log");
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

error_reporting(E_ALL);
ini_set("error_reporting","E_ALL");
ini_set("error_log", "log.txt"); // расположение лог-файла ошибок
ini_set("log_errors", "true"); // включить лог ошибок
error_log( "Hello, errors!" ); // записать в лог-файл значение/строку
//trigger_error("error",E_USER_ERROR);

$log = new Logger('name');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
$log->pushProcessor(new \Monolog\Processor\WebProcessor());
$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

print('<pre>');
var_dump('hello 1111111');
//var_dump($i = 5/0);
//echo phpinfo();



$log->debug(
    'req',
    [
        'req' => $_REQUEST,
    ]
);

