<?php

declare(strict_types=1);

namespace HttpInterface;

include dirname(__DIR__) . './../vendor/autoload.php';

use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Monolog\Handler\StreamHandler;

/**
 * Class CalculateBonus - выполняют расчёт и выполнение скидки.
 * @package Numbers
 */
class CalculateBonus
{
    private $idOvner;
    private $clientData;
    private $products;
    private $opportunity;
    private $orderId;
    private $bonusValue;

    private $log;
    private $client;

    /**
     * OrderAllData constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
        $this->log = new Logger('name');
        $this->log->pushHandler(new StreamHandler('logs/b24-api-client-debug.log', Logger::DEBUG));
        $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));

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
    public function setIdOrderOvner($idOvner)
    {
        $this->idOvner = $idOvner;
    }

    /**
     * Получить идентификатор заказчика
     * @return - идентификатор заказчика
     */
    public function getIdOrderOvner()
    {
        return $this->idOvner;
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
     * Получить из запроса данные и разобрать их
     */
    public function allOrderData()
    {
        try {
            $core = (new \Bitrix24\SDK\Core\CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-cdukpe.bitrix24.ru/rest/1/o1aiaw6ifekz1ryy/')
                ->build();


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
            $productsInfo = [];
            $discountForOnePosition = $this->getBonus()/count($this->getProducts());

            $ostatok = 0;
            $tablePart = [];
            foreach ($this->getProducts() as $position) {
               // $productsInfo[$discountForOnePosition] = $position['QUANTITY'];
                $pOld = $position['PRICE'];
                if ($pOld < $discountForOnePosition) {
                    $ostatok += $discountForOnePosition - $pOld;
                    $position['DISCOUNT_SUM'] = $discountForOnePosition / $position['QUANTITY'];
                }
                $tablePart[] = $position;
            }


        } catch (\Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }
}