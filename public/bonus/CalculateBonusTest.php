<?php

include dirname(__DIR__) . './../vendor/autoload.php';
require_once 'CalculateBonus.php';

use Monolog\Logger;
use bonus\CalculateBonus;
use Monolog\Processor\MemoryUsageProcessor;
use PHPUnit\Framework\TestCase;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;


/**
 * Тестовый класс для проверки расчёту бонусов.
 *
 * Class CalculateBonusTest
 */
class CalculateBonusTest extends TestCase
{
    public function testBonusCalculate(): void
    {
        $log = new Logger('bonus');
        $log->pushHandler(new StreamHandler(__DIR__ . '/test.log', Logger::DEBUG));
        $log->pushHandler(new FirePHPHandler());
        $log->pushProcessor(new MemoryUsageProcessor(true, true));
        $log->info('My logger is now ready');
        // Запись текстовых данных в файл. - формирование табличной части заказа
        /* $log = new Logger('name');
          $log->pushHandler(new StreamHandler('logs/test.log', Logger::DEBUG));
          $log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
          $client = HttpClient::create(['http_version' => '2.0']);
          $core = (new \Bitrix24\SDK\Core\CoreBuilder())
              ->withLogger($log)
              ->withWebhookUrl('https://b24-cdukpe.bitrix24.ru/rest/1/o1aiaw6ifekz1ryy/')
              ->build();


          $res = $core->call('crm.deal.productrows.get',['ID'=> 12]);
          var_dump($res->getResponseData()->getResult()->getResultData());
          $mass = $res->getResponseData()->getResult()->getResultData();
          $fileName = 'content2.txt';
          file_put_contents($fileName,$mass);*/


        //  $json = json_encode($mass);
        $fileName = 'content1.txt';
        $data = json_decode(file_get_contents($fileName));
        $array = [];
        foreach ($data as $value) {
            $array[] = (array)$value;
        }

        $bonusCalculator = new CalculateBonus(
            1, 2, $array,
            45, 100, 30, $log
        );
        print_r($bonusCalculator->getNewTablePart());
        // Расчёт остатка бонусов
        // 45(стоимость сделки) / 100 * 30(макс процент скидки) = 13.5
        //100(все бонусы) - 13.5 = 86.5
        $this->assertEquals(86.5, $bonusCalculator->calculateAndDiscount());
    }

    public function testOrderTablePartBonusCalculate(): void
    {
        $log = new Logger('bonus');
        $log->pushHandler(new StreamHandler(__DIR__ . '/test.log', Logger::DEBUG));
        $log->pushHandler(new FirePHPHandler());
        $log->pushProcessor(new MemoryUsageProcessor(true, true));
        $log->info('My logger is now ready');

        $fileName = 'content1.txt';
        $data = json_decode(file_get_contents($fileName));
        $array = [];
        foreach ($data as $value) {
            $array[] = (array)$value;
        }

        print_r($array);
        try {
            $bonusCalculator = new CalculateBonus(
                1, 2, $array,
                45, 100, 30, $log
            );
        } catch (Exception $e) {
            $log->info('Ошибка - ' . $e->getMessage());
        }
        $bonusCalculator->calculateAndDiscount();
        // Расчёт остатка бонусов
        // 45(стоимость сделки) / 100 * 30(макс процент скидки) = 13.5
        // 20(стоимость первого товара в заказе) - 13.5 = 6.5

        // Первый товар в заказе выбирается логикой алгоритма - если вся скидка,
        // не превышает цены каждого товара(в данном случае первого) товара
        // и остаётся ещё остаток - то вся скидка умещается в первом товаре.
        //print_r($bonusCalculator->getNewTablePart());
        $this->assertEquals(13.5, $bonusCalculator->getNewTablePart()[0]['DISCOUNT_SUM']);
        $this->assertEquals(6.5, $bonusCalculator->getNewTablePart()[0]['PRICE_EXCLUSIVE']);
    }

    public function testBonusCalculateBigOrder3positionLAstPositionIsLargest(): void
    {
        $log = new Logger('bonus');
        $log->pushHandler(new StreamHandler(__DIR__ . '/test.log', Logger::DEBUG));
        $log->pushHandler(new FirePHPHandler());
        $log->pushProcessor(new MemoryUsageProcessor(true, true));
        $log->info('My logger is now ready');
        // Запись текстовых данных в файл. - формирование табличной части заказа
        /* $log = new Logger('name');
         $log->pushHandler(new StreamHandler('logs/test.log', Logger::DEBUG));
         $log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
         $client = HttpClient::create(['http_version' => '2.0']);
         $core = (new \Bitrix24\SDK\Core\CoreBuilder())
             ->withLogger($log)
             ->withWebhookUrl('https://b24-cdukpe.bitrix24.ru/rest/1/o1aiaw6ifekz1ryy/')
             ->build();


         $res = $core->call('crm.deal.productrows.get',['ID'=> 244]);
         var_dump($res->getResponseData()->getResult()->getResultData());
         $mass = $res->getResponseData()->getResult()->getResultData();
         $fileName = 'content2.txt';
         $json = json_encode($mass);
         file_put_contents($fileName,$json);*/

        $fileName = 'content2.txt';
        $data = json_decode(file_get_contents($fileName));
        $array = [];
        foreach ($data as $value) {
            $array[] = (array)$value;
        }

        print_r($array);
        $bonusCalculator = new CalculateBonus(
            1, 1, $array,
            1028, 400, 30, $log
        );
        // Расчёт остатка бонусов
        // 745(стоимость сделки) / 100 * 30(макс процент скидки) = 308.4
        // 400(все бонусы) - 308.4 = 91.6
        // Скидка будет распределяться по всем товарам, так как стоимость каждого из них
        // кроме последнего будет меньше скидки
        // 20(стоимость первого товара в заказе) - 19(скидка) = 1
        // 25(стоимость второго товара в заказе) - 23,75(скидка)  = 1,25
        // 983(стоимость третьего товара в заказе) - 265,65(скидка)  = 717.35


        $this->assertEquals(91.6, $bonusCalculator->calculateAndDiscount());
        $this->assertEquals(19, $bonusCalculator->getNewTablePart()[0]['DISCOUNT_SUM']);
        $this->assertEquals(1, $bonusCalculator->getNewTablePart()[0]['PRICE_EXCLUSIVE']);

        $this->assertEquals(23.75, $bonusCalculator->getNewTablePart()[1]['DISCOUNT_SUM']);
        $this->assertEquals(1.25, $bonusCalculator->getNewTablePart()[1]['PRICE_EXCLUSIVE']);

        $this->assertEquals(265.65, $bonusCalculator->getNewTablePart()[2]['DISCOUNT_SUM']);
        $this->assertEquals(717.35, $bonusCalculator->getNewTablePart()[2]['PRICE_EXCLUSIVE']);
    }
}

