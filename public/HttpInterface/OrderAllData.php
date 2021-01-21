<?php

declare(strict_types=1);

namespace HttpInterface;

include dirname(__DIR__) . './../vendor/autoload.php';

use Bitrix24\SDK\Core\CoreBuilder;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Symfony\Component\HttpClient\HttpClient;
use Monolog\Handler\StreamHandler;
use Throwable;

/**
 * Class OrderAllData - Данные по заказу
 * @package Numbers
 */
class OrderAllData
{
    private $orderId;
    private $stage;
    private $idOwner;
    private $log;
    private $clientData;
    private $products;
    private $opportunity;

    /**
     * OrderAllData constructor - Конструктор класса
     * @param $orderId - идентификатор заказа
     */
    public function __construct(int $orderId, $log)
    {
        $this->orderId = $orderId;
        $this->log = $log;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param mixed $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * Задать идентификатор заказа
     * @param - идентификатор заказа
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
     * Задать данные заказа
     * @param $stage - стадия заказа
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
    }

    /**
     * Получить стадию заказа
     * @return mixed - стадия заказа
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Задать идентификатор заказчика
     * @param $idOwner - идентификатор заказчика
     */
    public function setIdOrderOwner($idOwner)
    {
        $this->idOwner = $idOwner;
    }

    /**
     * Получить идентификатор заказчика
     * @return mixed - идентификатор заказчика
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
     * @return mixed - массив данных клиента
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
     * @return mixed - массив табличной части сделки
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
     * @return mixed - сумму сделки
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
            $core = (new CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-r1mql2.bitrix24.ru/rest/1/yn57uv4t4npz440h/')
                ->build();


            $res = $core->call('crm.deal.get', ['ID' => $this->orderId]);

            $arrayOrderData = $res->getResponseData()->getResult()->getResultData();
            print_r($arrayOrderData);
            $this->setIdOrderOwner((int)$arrayOrderData['CONTACT_ID']);
            $this->setStage($arrayOrderData['STAGE_ID']);
            $this->setOpportunity($arrayOrderData['OPPORTUNITY']);

            var_dump($res->getResponseData()->getResult()->getResultData());

            $res = $core->call('crm.contact.get', ['ID' => $this->idOwner]);
            $this->setClientData($res->getResponseData()->getResult()->getResultData());
            var_dump($this->getClientData());
            $res = $core->call('crm.deal.productrows.get', ['ID' => $this->orderId]);
            $this->setProducts($res->getResponseData()->getResult()->getResultData());
            var_dump($this->getProducts());
        } catch (Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }
}
