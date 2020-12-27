<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once("HttpInterface/ParseNewOrder.php");

require_once('./db/Query.php');
require_once('db/ConnectDB.php');
require_once('bonus/CalculateBonus.php');
require_once("HttpInterface/ParseNewOrder.php");

use HttpInterface\ParseNewOrder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use db\Query;
use bonus\CalculateBonus;


$log = new Logger('name');
$log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
$log->pushProcessor(new \Monolog\Processor\WebProcessor());
$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

print('<pre>');
var_dump('hello 1111111');

error_reporting(E_ALL);
var_dump(ini_set('error_reporting', 'E_ALL'));
var_dump(ini_set('log_errors', '1')); // включить лог ошибок
var_dump(ini_set('error_log', __DIR__.'/log.txt')); // расположение лог-файла ошибок
error_log('Hello, errors!'); // записать в лог-файл значение/строку
//phpinfo();
$order = new ParseNewOrder();
$dataRequest = $order->makeOrderData();

$log->debug(
    'req',
    [
        'req' => $dataRequest,
    ]
);

if (class_exists('ConnectDB')){
    $log->debug("Класс ConnectDB");
} else {
    $log->debug("Нет класса ConnectDB");
}
if (class_exists('db\Query')){
    $log->debug("Класс Query");
} else {
    $log->debug("Нет класса Query");
}
$connection = new ConnectDB();
$pdo = $connection->getPDO();
$log->debug('Таблицы в БД:',
            [
                'Таблицы в БД:' => $connection->getTables(),
            ]);
$person = new db\Query($pdo);

switch ($dataRequest['ID_STAGE']) {
    case 'C2:NEW' : {
        $log->debug("Проверка регистрации пользователя ".$dataRequest['ID_CLIENT']);

        $respons = $person->isOwnerRegistred($dataRequest['ID_CLIENT']);
        $log->debug('Проверка регистрации пользователя в БД - ',
                    [
                        'Проверка регистрации пользователя в БД - ' => $respons,
                    ]);
        if ($respons != null) {
            $log->debug("Обработка пользователя :");
            if($respons == 0) {
                $log->debug("Добавление пользователя:");
                $add = $person->addOwner($dataRequest['ID_CLIENT']);
                $person->writeDate($dataRequest['ID_CLIENT'],"Register in system. 200 bonuses add.");
                $log->debug('Пользователь добавлен в БД - ',
                            [
                                'Пользователь добавлен в БД - ' => $add,
                            ]);
            } else {
                $log->debug("Пользователь зарегистрирован :");
            }
        } else {
            $log->debug("Пользователь NULL - ");
        }
        break;
    }
    case 'C2:PREPAYMENT_INVOICE' : {
        $log->debug("Разплачиваемся бонусами");
        $respons = $person->getBonusCount($dataRequest['ID_CLIENT']);
        $log->debug('Этап скидки. Бонусов - ',
                    [
                        'Этап скидки. Бонусов - ' => $respons,
                    ]);
        $discountPersent = $person->getMaxDiscauntPersent($dataRequest['ID_CLIENT']);
        $log->debug('Максимальная скидка - ',
                    [
                        'Максимальная скидка - ' => $discountPersent,
                    ]);

        if (class_exists('CalculateBonus')){
            $log->debug("Класс CalculateBonus");
        } else {
            $log->debug("Нет класса CalculateBonus");
        }

        $stage = $person->setStage($dataRequest['id'], $dataRequest['ID_STAGE']);

        $log->debug('Этап установлен - ',
                    [
                        'Этап установлен - ' => $stage,
                    ]);

        $log->debug("Начинается вычисление и начисление бонусов :");
        $bonusCalculator = new CalculateBonus($dataRequest['id']);
        $bonusCalculator->setOpportunity($dataRequest['PRICE_ORDER']);
        $bonusCalculator->setBonus($respons);
        $bonusCalculator->setProducts($dataRequest['Products']);
        $bonusCalculator->setIdOrderOwner($dataRequest['ID_CLIENT']);
        $bonusCalculator->setDiscaountPersentage($discountPersent);
        $newBonuses = $bonusCalculator->calculateAndDiscount();
        $log->debug('Оставшиеся бонусы - ',
                    [
                        'Оставшиеся бонусы - ' => $newBonuses,
                    ]);


        $bonusesAfterWrite = $person->writeRemainsBonuses($dataRequest['ID_CLIENT'],$newBonuses);
        $log->debug('Остатки записаны в бд - ',
                    [
                        'Остатки записаны в бд - ' => $bonusesAfterWrite,
                    ]);
        $person->writeDate($dataRequest['ID_CLIENT'],"Bonuses are debited. Remains: ".$bonusesAfterWrite);



        break;

    }
    case 'C2:WON' : {
        $log->debug("Сделка завершена");
        $respons = $person->getStage($dataRequest['id']);
        $log->debug('Этап сделки - ',
                    [
                        'Этап сделки - ' => $respons,
                    ]);
        if (($respons == null) || ($respons != 'C2:PREPAYMENT_INVOICE') ) {
            $rule = $person->getRule();
            $log->debug('Правило - ',
                        [
                            'Правило - ' => $rule,
                        ]);
            $bonuses = $person->getBonusCount($dataRequest['ID_CLIENT']);
            $log->debug('Этап начисления. Бонусов - ',
                        [
                            'Этап начисления. Бонусов - ' => $bonuses,
                        ]);
            $newBonuses = $person->accrualBonuses($dataRequest['ID_CLIENT'], $dataRequest['PRICE_ORDER'], $rule, $bonuses);
            $log->debug('Этап начисления. Добавлены- ',
                        [
                            'Этап начисления. Добавлены - ' => $newBonuses,
                        ]);
            $person->writeDate($dataRequest['ID_CLIENT'],"Add bonuses - ".$newBonuses);
        } else {
            $log->debug("Была скидка, и бонусы начислять нельзя");
        }
        $log->debug('Завершение сделки без начисления - ',
                    [
                        'Завершение сделки без начисления - ID ' => $dataRequest['id'],
                    ]);

        break;

    }
    default: {
        $log->debug('Этап - ' .$dataRequest['ID_STAGE']);
    }
}
