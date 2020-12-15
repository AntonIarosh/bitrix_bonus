<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;


$log = new Logger('name');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
$log->pushProcessor(new \Monolog\Processor\WebProcessor());
$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

print('<pre>');
var_dump('hello 1111111');
echo phpinfo();
ini_set("log_errors", 1); // включить лог ошибок
ini_set("error_log", "../../php-error.log"); // расположение лог-файла ошибок
error_log( "Hello, errors!" ); // записать в лог-файл значение/строку

$log->debug(
    'req',
    [
        'req' => $_REQUEST,
    ]
);

