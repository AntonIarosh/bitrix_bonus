<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once('HttpInterface/ParseNewOrder.php');

require_once('./db/Query.php');
require_once('db/ConnectDB.php');
require_once('db/BonusAndDiscountPrograms.php');
require_once('bonus/CalculateBonus.php');
require_once('bonus/SellBonus.php');
require_once('bonus/CalculateDiscount.php');
require_once('HttpInterface/MakePresent.php');

use HttpInterface\ParseNewOrder;
use HttpInterface\MakePresent;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use db\Query;
use bonus\CalculateBonus;
use bonus\CalculateDiscount;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use PHPUnit\Util\ErrorHandler;
use bonus\SellBonus;


$log = new Logger('name');
try {
    $log->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/webhook.log', Logger::DEBUG));
} catch (Exception $e) {
    $log->debug('Ошибка - '). $e->getMessage();
}
$log->pushProcessor(new MemoryUsageProcessor(true, true));
$log->pushProcessor(new WebProcessor());
$log->pushProcessor(new IntrospectionProcessor());

error_reporting(E_ALL);
ini_set('error_reporting', 'E_ALL');
ini_set('log_errors', '1'); // включить лог ошибок
ini_set('error_log', __DIR__ . '/log.txt'); // расположение лог-файла ошибок
error_log('Hello, errors!'); // записать в лог-файл значение/строку

$log->debug('Обращение к файлу');
$order = new ParseNewOrder($log);
$dataRequest = $order->makeOrderData();
$log->debug('Разбор запроса: ');
$log->debug(
    'req',
    [
        'req' => $dataRequest,
    ]
);

$connection = new ConnectDB();
$pdo = $connection->getPDO();

$dbAgent = new db\Query($pdo, $log);
$programs = new \db\BonusAndDiscountPrograms();
$log->debug(
    'Программы',
    [
        'бонусы - ' => $programs->getBonusForNewOwner(),
        'скидка - ' => $programs->getRule(),
    ]
);

switch ($dataRequest['ID_STAGE']) {
    case 'C1:NEW' :
    {
        $log->debug('Проверка регистрации пользователя ' . $dataRequest['ID_CLIENT']);

        $respons = $dbAgent->isOwnerRegistred($dataRequest['ID_CLIENT']);
        $log->debug(
            'Проверка регистрации пользователя в БД - ',
            [
                'Проверка регистрации пользователя в БД - ' => $respons,
            ]
        );
        if ($respons != null) {
            $log->debug('Обработка пользователя :');
            if ($respons == 0) {
                $log->debug('Добавление пользователя:');
                $add = $dbAgent->addOwner($dataRequest['ID_CLIENT'], $programs->getBonusForNewOwner());
                $dbAgent->writeDate($dataRequest['ID_CLIENT'], 'Register in system. 200 bonuses add.');
                $log->debug(
                    'Пользователь добавлен в БД - ',
                    [
                        'Пользователь добавлен в БД - ' => $add,
                    ]
                );
            } else {
                $log->debug('Пользователь зарегистрирован :');
            }
        } else {
            $log->debug('Пользователь NULL - ');
        }
        break;
    }
    case 'C1:PREPAYMENT_INVOICE' :
    {
        $log->debug('Разплачиваемся бонусами');

        $respons = $dbAgent->getBonusCount($dataRequest['ID_CLIENT']);
        $log->debug(
            'Этап скидки. Бонусов - ',
            [
                'Этап скидки. Бонусов - ' => $respons,
            ]
        );
        $discountPersent = $dbAgent->getMaxDiscauntPersent($dataRequest['ID_CLIENT']);
        $log->debug(
            'Максимальная скидка - ',
            [
                'Максимальная скидка - ' => $discountPersent,
            ]
        );

        if (class_exists('CalculateBonus')) {
            $log->debug('Класс CalculateBonus');
        } else {
            $log->debug('Нет класса CalculateBonus');
        }

        $stage = $dbAgent->setStage($dataRequest['id'], $dataRequest['ID_STAGE']);

        $log->debug(
            'Этап установлен - ',
            [
                'Этап установлен - ' => $stage,
            ]
        );

        $log->debug('Начинается вычисление и начисление бонусов :');
        try {
            $bonusCalculator = new CalculateBonus((int)$dataRequest['id'], (int)$dataRequest['ID_CLIENT'], $dataRequest['Products'],
                                                  $dataRequest['PRICE_ORDER'], (float)$respons, (int)
                                                  $discountPersent, $log);

            $newBonuses = $bonusCalculator->calculateAndDiscount();
            $seller = new \bonus\SellBonus((int)$dataRequest['id'], $bonusCalculator->getNewTablePart(), $log);
            $seller->makeSellBonuses();
            $log->debug(
                'Оставшиеся бонусы - ',
                [
                    'Оставшиеся бонусы - ' => $newBonuses,
                ]
            );
        } catch (Exception $e) {
            $log->debug('Ошибка - '). $e->getMessage();
        }

        $bonusesAfterWrite = $dbAgent->writeRemainsBonuses($dataRequest['ID_CLIENT'], $newBonuses);
        $log->debug(
            'Остатки записаны в бд - ',
            [
                'Остатки записаны в бд - ' => $bonusesAfterWrite,
            ]
        );
        $dbAgent->writeDate($dataRequest['ID_CLIENT'], 'Bonuses are debited. Remains: ' . $bonusesAfterWrite);


        break;
    }
    case 'C1:WON' :
    {
        $log->debug('Сделка завершена');
        $respons = $dbAgent->getStage($dataRequest['id']);
        $log->debug(
            'Этап сделки - ',
            [
                'Этап сделки - ' => $respons,
            ]
        );
        if (($respons == null) || ($respons != 'C1:PREPAYMENT_INVOICE')) {
            $nameOfRule = $programs->getRule();
            $rule = $dbAgent->getRule($nameOfRule);
            $log->debug(
                'Правило - ',
                [
                    'Правило - ' => $rule,
                ]
            );
            $bonuses = $dbAgent->getBonusCount($dataRequest['ID_CLIENT']);
            $log->debug(
                'Этап начисления. Бонусов - ',
                [
                    'Этап начисления. Бонусов - ' => $bonuses,
                ]
            );
            $discountCalculator = new CalculateDiscount($dataRequest['PRICE_ORDER'], $rule, $bonuses, $log);

            $newBonuses = $dbAgent->accrualBonuses(
                $dataRequest['ID_CLIENT'],
                $discountCalculator->accrualBonuses()
            );
            $log->debug(
                'Этап начисления. Добавлены- ',
                [
                    'Этап начисления. Добавлены - ' => $newBonuses,
                ]
            );
            $dbAgent->writeDate($dataRequest['ID_CLIENT'], 'Add bonuses - ' . $newBonuses);
        } else {
            $log->debug('Была скидка, и бонусы начислять нельзя');
        }
        if ($dataRequest['PRICE_ORDER'] > 4000) {
            $present = new MakePresent((int)$dataRequest['id'], (int)$dataRequest['ID_CLIENT'], $log);
            $present->calculatePresents();
            $present->makePresents($present->getPresents());
            $log->debug('Подарок добавлен');
        }
        $log->debug(
            'Завершение сделки без начисления - ',
            [
                'Завершение сделки без начисления - ID ' => $dataRequest['id'],
            ]
        );
        break;
    }
    default:
    {
        $log->debug('Этап - ' . $dataRequest['ID_STAGE']);
    }
}
