<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once("HttpInterface/ParseNewOrder.php");

require_once("db/Query.php");
require_once("db/ConnectDB.php");
require_once("HttpInterface/ParseNewOrder.php");

use HttpInterface\ParseNewOrder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use db\Query;


$log = new Logger('name');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
$log->pushProcessor(new \Monolog\Processor\WebProcessor());
$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

print('<pre>');
var_dump('hello 1111111');
error_reporting(E_ALL);
ini_set('error_reporting', 'E_ALL');
ini_set('error_log', 'log.txt'); // расположение лог-файла ошибок
ini_set('log_errors', 'true'); // включить лог ошибок
error_log('Hello, errors!'); // записать в лог-файл значение/строку

$order = new ParseNewOrder();
$dataRequest = $order->makeOrderData();

$log->debug(
    'req',
    [
        'req' => $dataRequest,
    ]
);

switch ($dataRequest['ID_STAGE']) {
    case 'C2:NEW' : {
        $log->debug("Проверка регистрации пользователя ".$dataRequest['ID_CLIENT']);
        if (class_exists('ConnectDB')){
            $log->debug("Класс ConnectDB");
        } else {
            $log->debug("Нет класса ConnectDB");
        }
        if (class_exists('Query')){
            $log->debug("Класс Query");
        } else {
            $log->debug("Нет класса Query");
        }
        $connection = new ConnectDB();
        $pdo = $connection->getPDO();

        $person = new Query($pdo);
        $respons = $person->isOwnerRegistred($dataRequest['ID_CLIENT']);
        $log->debug("Проверка регистрации пользователя в БД - ".$respons);
        if ($respons != null) {
            if($respons === 0) {
                $add = $person->addOwner($dataRequest['ID_CLIENT']);
                $log->debug("Пользователь добавлен в БД - ".$add);
            }
        }
        break;
    }
    case 'C2:PREPAYMENT_INVOICE' : {
        $log->debug("Разплачиваемся бонусами");
        break;
    }
    case 'C2:WON' : {
        $log->debug("Сделка завершена");
        break;
    }
    default: {
        $log->debug('Этап - ' .$dataRequest['ID_STAGE']);
    }
}
