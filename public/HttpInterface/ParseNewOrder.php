<?php

declare(strict_types=1);

namespace HttpInterface;

include dirname(__DIR__) . './../vendor/autoload.php';
require_once 'OrderAllData.php';

use PhpParser\Error;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ParseNewOrder - Обрабатывает сработавший вэбхук и
 * получается идентификатор заказа
 * @package Numbers
 */
class ParseNewOrder
{
    private int $orderId;
    private $orderData;

    public const DOC_ID_PLACE = 1;
    public const DOC_ID_PLACE_IN_ALL_IFO = 2;

    /**
     * ParseNewOrder constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     * @param array $orderData - данные заказа
     */
public function __construct($orderData = 0, $orderId = 0)
    {
        $this->orderId = $orderId;
        $this->orderData = $orderData;
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
     * @return int - идентификатор заказа
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
    /**
     * Задать данные заказа
     * @param $orderData - данные заказа
     */
    public function setOrderData($orderData)
    {
        $this->orderData = $orderData;
    }

    /**
     * Получить данные заказа
     * @return array|int - данные заказа
     */
    public function getOrderData()
    {
        return $this->orderData;
    }

    /**
     * Получить из запроса данные и разобрать их
     * @return array - данные заказа
     */
    public function makeOrderData(): array
    {
        $request = Request::createFromGlobals();

        $data = [];

        $data['DocId']  = $request->get('document_id');
        $data['id'] = $data['DocId'][2];

        $requestParams = explode('_', $data['DocId'][self::DOC_ID_PLACE_IN_ALL_IFO]);
        $this->setOrderId($requestParams[self::DOC_ID_PLACE]);
        $data['id'] = $this->getOrderId();

        $allOrderData = new OrderAllData($this->getOrderId());
        $allOrderData->allOrderData();
        $data['ID_CLIENT'] = $allOrderData->getIdOrderOvner();
        $data['ID_STAGE'] = $allOrderData->getStage();
        $data['Products'] = $allOrderData->getProducts();
        $data['All_client_INFO'] = $allOrderData->getClientData();
        $data['PRICE_ORDER'] = $allOrderData->getOpportunity();
        $data['СКИДКА'] = $allOrderData->getOpportunity()/100*30;

        print_r($data);
        return $data;
    }
}