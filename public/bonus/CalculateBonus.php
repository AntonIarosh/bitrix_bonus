<?php

declare(strict_types=1);

namespace bonus;

include dirname(__DIR__) . './../vendor/autoload.php';

use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

/**
 * Class CalculateBonus - выполняют расчёт и выполнение скидки.
 * @package Numbers
 */
class CalculateBonus
{
    private $idOwner;
    private $clientData;
    private $products;
    private $opportunity;
    private $orderId;
    private $bonusValue;

    private $discaountPersentage;

    private $log;
    private $client;

    /**
     * OrderAllData constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
        $this->log = new Logger('bonus');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Logger::DEBUG));
        $this->log->pushHandler(new FirePHPHandler());
        $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));
        $this->log->info('My logger is now ready');
        $this->client = HttpClient::create(['http_version' => '2.0']);
    }

    /**
     * Задать идентификатор заказа
     * @param $id - идентификатор заказа
     */
    public function setOrderId($id)
    {
        $this->orderId = $id;
    }

    /**
     * Получить идентификатор заказа
     * @return - идентификатор заказа
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Получить процент максимальной скидки
     * @return - процент максимальной скидки
     */
    public function getDiscaountPersentage()
    {
        return $this->discaountPersentage;
    }

    /**
     * Установить процент максимальной скидки
     * @param $discaountPersentage - процент максимальной скидки
     */
    public function setDiscaountPersentage($discaountPersentage)
    {
        $this->discaountPersentage = $discaountPersentage;
    }

    /**
     * Задать количество бонусов.
     * @param $bonusValue - стадия заказа
     */
    public function setBonus($bonusValue)
    {
        $this->bonusValue = $bonusValue;
    }

    /**
     * Получить количество бонусов
     * @return - бонусы клиента
     */
    public function getBonus()
    {
        return $this->bonusValue;
    }

    /**
     * Задать идентификатор заказчика
     * @param $idOvner - идентификатор заказчика
     */
    public function setIdOrderOwner($idOwner)
    {
        $this->idOwner = $idOwner;
    }

    /**
     * Получить идентификатор заказчика
     * @return - идентификатор заказчика
     */
    public function getIdOrderOwner()
    {
        return $this->idOwner;
    }

    /**
     * Задать данные клиента
     * @param $clientData - данные клиента
     */
    public function setClientData($clientData)
    {
        $this->clientData = $clientData;
    }

    /**
     * Получить данные клиента
     * @return - массив данных клиента
     */
    public function getClientData()
    {
        return $this->clientData;
    }

    /**
     * Задать данные продуктов сделки
     * @param $products - продуктов сделки
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * Получить данные продуктов сделки
     * @return - массив табличной части сделки
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Задать данные возможности сделки
     * @param $opportunity - возможности сделки
     */
    public function setOpportunity($opportunity)
    {
        $this->opportunity = $opportunity;
    }

    /**
     * Получить данные возможности сделки
     * @return - сумму сделки
     */
    public function getOpportunity()
    {
        return $this->opportunity;
    }

    /**
     * Расчитать и выполнить скидку на заказ
     */
    public function calculateAndDiscount()
    {
        try {
            $core = (new \Bitrix24\SDK\Core\CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-cdukpe.bitrix24.ru/rest/1/o1aiaw6ifekz1ryy/')
                ->build();

            $this->log->debug('Бонусы были - ',
                              [
                                  'Бонусы были - ' => $this->getBonus(),
                              ]);
            /*$res = $core->call('crm.deal.get',['ID'=>$this->orderId]);

            $arrayOrderData = $res->getResponseData()->getResult()->getResultData();
            print_r($arrayOrderData);
            $this->setIdOrderOvner($arrayOrderData['CONTACT_ID']);
            $this->setStage($arrayOrderData['STAGE_ID']);
            $this->setOpportunity($arrayOrderData['OPPORTUNITY']);

            var_dump($res->getResponseData()->getResult()->getResultData());

            $res = $core->call('crm.contact.get',['ID' => $this->idOvner]);
            $this->setClientData($res->getResponseData()->getResult()->getResultData());
            var_dump($this->getClientData());
            $res = $core->call('crm.deal.productrows.get',['ID' => $this->orderId]);
            $this->setProducts($res->getResponseData()->getResult()->getResultData());
            var_dump($this->getProducts());*/
            $remains = 0;
            $allDiscount = 0;
            $discount = $this->getOpportunity()/100 * $this->getDiscaountPersentage();
            if ($discount < $this->getBonus()) {
                $remains = $this->getBonus() - $discount;
                $allDiscount = $discount;
            } else {
                $allDiscount = $this->getBonus();
            }
            $productsInfo = [];
            $this->log->debug('Остатки бонусной суммы - ',
                              [
                                  'Остатки бонусной суммы - ' => $remains,
                              ]);
            //$discountForOnePosition = $allDiscount/count($this->getProducts());
            $this->log->debug('Сумма скидки - ',
                              [
                                  'Сумма скидки - ' => $allDiscount,
                              ]);
            $this->log->debug('Исходные данные - ',
                              [
                                  'Исходные данные - ' => $this->getProducts(),
                              ]);

            $discountNumber = 0;
            $tablePart = [];
            $oldTablePart = $this->getProducts();
            for ($i =0; $i < count($oldTablePart); $i++) {
                // Попытка записать всю скидку в текущию позицию
                if ($allDiscount < $oldTablePart[$i]['PRICE'] * $oldTablePart[$i]['QUANTITY'] ) {
                    $oldTablePart[$i]['DISCOUNT_SUM'] = $allDiscount/$oldTablePart[$i]['QUANTITY'] ;
                    $oldTablePart[$i]['PRICE_EXCLUSIVE'] = $oldTablePart[$i]['PRICE'] - $oldTablePart[$i]['DISCOUNT_SUM'];
                    $allDiscount -= $oldTablePart[$i]['DISCOUNT_SUM'] * $oldTablePart[$i]['QUANTITY'] ;
                    $discountNumber += $oldTablePart[$i]['DISCOUNT_SUM']  * $oldTablePart[$i]['QUANTITY'] ;
                    $tablePart[] = $oldTablePart[$i];
                    continue;
                }


                // Попытка делить скидку на все позиции
                $discountForOnePosition = $allDiscount/(count($oldTablePart) - $i);
                $this->log->debug('скидка на эту строку тч - ',
                                  [
                                      'скидка на эту строку тч - ' => $discountForOnePosition,
                                      'позиций в  тч - ' => count($oldTablePart),
                                      'позиция - ' => count($oldTablePart) - $i,
                                  ]);
                $pOld = $oldTablePart[$i]['PRICE'];
                if ($pOld < $discountForOnePosition) {
                    $this->log->debug('цена товара в тч меньше скидки - ',
                                      [
                                          'цена товара - ' => $pOld,
                                          'скидка - ' => $discountForOnePosition,
                                      ]);
                    //$remains += $discountForOnePosition - $pOld;
                    $oldTablePart[$i]['DISCOUNT_SUM'] = ($pOld / $oldTablePart[$i]['QUANTITY'])/100 * 95;
                    $this->log->debug('Простая сумма скидки - ',
                                      [
                                          'Простая сумма скидки - ' => $oldTablePart[$i]['DISCOUNT_SUM'],
                                      ]);
                    $wholeDevision = floor($pOld / $oldTablePart[$i]['QUANTITY']);
                    $remainderOfDevision =  fmod($pOld / $oldTablePart[$i]['QUANTITY'], 1); ;   // 321 ;
                    $sum = $wholeDevision+$remainderOfDevision;
                    $this->log->debug('Сложная сумма скидки - ',
                                      [
                                          'Сложная сумма скидки - ' => $sum,
                                          'Делим - ' => $pOld,
                                          'На - ' => $oldTablePart[$i]['QUANTITY'],
                                          'Целая - ' => $wholeDevision,
                                          'Дробная - ' => $remainderOfDevision,
                                          'Сложная сумма скидки 2 - ' => $wholeDevision+$remainderOfDevision,
                                      ]);
                    $oldTablePart[$i]['PRICE_EXCLUSIVE'] = $pOld - $oldTablePart[$i]['DISCOUNT_SUM'];
                    $discountNumber += $oldTablePart[$i]['DISCOUNT_SUM'];
                    $allDiscount -= $oldTablePart[$i]['DISCOUNT_SUM'] * $oldTablePart[$i]['QUANTITY'];
                    $this->log->debug('Сумма всей скидки - ',
                                      [
                                          'после вычитания позиции тч - ' => $allDiscount,
                                      ]);
                    if ($i == count($oldTablePart)-1) {
                        $remains += $allDiscount;
                        $this->log->debug('Остатки бонусной суммы - ',
                                          [
                                              'Остатки бонусной суммы - ' => $remains,
                                          ]);
                    }
                } else {
                    $this->log->debug('цена товара в тч больше скидки - ',
                                      [
                                          'цена товара - ' => $pOld,
                                          'скидка - ' => $discountForOnePosition,
                                      ]);
                    $oldTablePart[$i]['DISCOUNT_SUM'] = $discountForOnePosition / $oldTablePart[$i]['QUANTITY'];
                    $oldTablePart[$i]['PRICE_EXCLUSIVE'] = $pOld - $oldTablePart[$i]['DISCOUNT_SUM'];
                    $discountNumber += $oldTablePart[$i]['DISCOUNT_SUM'];
                    $allDiscount -= $oldTablePart[$i]['DISCOUNT_SUM'] * $oldTablePart[$i]['QUANTITY'];
                    $this->log->debug('Сумма всей скидки - ',
                                      [
                                          'после вычитания позиции тч - ' => $allDiscount,
                                      ]);

                    $this->log->debug('Простая сумма скидки - ',
                                      [
                                          'Простая сумма скидки - ' => $oldTablePart[$i]['DISCOUNT_SUM'],
                                          //'Простая сумма скидки - ' => $position['DISCOUNT_SUM'],
                                      ]);

                    $wholeDevision = floor($discountForOnePosition / $oldTablePart[$i]['QUANTITY']);
                    //$wholeDevision = floor($discountForOnePosition / $position['QUANTITY']);
                    //$remainderOfDevision = $discountForOnePosition % $position['QUANTITY'];
                    $remainderOfDevision =  fmod($discountForOnePosition / $oldTablePart[$i]['QUANTITY'], 1); ;   // 321 ;
                   // $remainderOfDevision =   $y = explode('.', discountForOnePosition / $position['QUANTITY'])[1];
                    $sum = $wholeDevision+$remainderOfDevision;
                    $this->log->debug('Сложная сумма скидки - ',
                                      [
                                          'Сложная сумма скидки - ' => $sum,
                                          'Делим - ' => $discountForOnePosition,
                                          'На - ' => $oldTablePart[$i]['QUANTITY'],
                                          //'На - ' => $position['QUANTITY'],
                                          'Целая - ' => $wholeDevision,
                                          'Дробная - ' => $remainderOfDevision,
                                          'Сложная сумма скидки 2 - ' => $wholeDevision+$remainderOfDevision,
                                      ]);
                }
                //$res = $core->call('crm.deal.productrows.set',['ID'=> $this->getOrderId(), 'ROWS'=> [$tablePart]]);
                $tablePart[] = $oldTablePart[$i];
                //$tablePart[] = $position;
            }
            $this->log->debug('Скидка остаток - ',
                              ['Скидка - остаток - ' => $allDiscount,]);
            $this->log->debug('Скидка  на - ',
                              ['Скидка  на' => $discountNumber,]);
            $this->log->debug('Массив для записи - ',
                        ['Массив для записи - ' => $tablePart,]);
            $res = $core->call('crm.deal.productrows.set',['ID'=> $this->getOrderId(), 'ROWS'=> $tablePart]);
            $this->log->debug('Бонусы стали - ',
                              ['Бонусы стали - ' => $remains,]);
            $this->log->debug('Скидка остаток - ',
                              ['Скидка - остаток - ' => $allDiscount,]);
            return $remains;

        } catch (\Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
            return $exception->getMessage();
        }
    }
}