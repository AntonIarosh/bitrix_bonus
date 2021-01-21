<?php

declare(strict_types=1);

namespace HttpInterface;

include dirname(__DIR__) . './../vendor/autoload.php';
require_once 'OrderAllData.php';

use Monolog\Logger;
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
    private Logger $log;

    public const DOC_ID_PLACE = 1;
    public const DOC_ID_PLACE_IN_ALL_IFO = 2;

    /**
     * ParseNewOrder constructor - Конструктор класса
     * @param Logger $log - лог
     * @param int $orderId - идентификатор заказа
     */
    public function __construct(Logger $log, int $orderId = 0)
    {
        $this->log = $log;
        $this->log->debug(
            'Неполные данные',
            [
                'Неполные данные' => 'Конструктор parse new order',
            ]
        );
        $this->orderId = $orderId;
    }

    /**
     * @return Logger
     */
    public function getLog(): Logger
    {
        return $this->log;
    }

    /**
     * @param Logger $log
     */
    public function setLog(Logger $log): void
    {
        $this->log = $log;
    }

    /**
     * Задать идентификатор заказа
     * @param $id - идентификатор заказа
     */
    public function setOrderId(int $id)
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
     * Получить из запроса данные и разобрать их
     * @return array - данные заказа
     */
    public function makeOrderData(): array
    {
        $request = Request::createFromGlobals();

        $data = [];

        $data['DocId'] = $request->get('document_id');
        $data['id'] = $data['DocId'][2];

        $requestParams = explode('_', $data['DocId'][self::DOC_ID_PLACE_IN_ALL_IFO]);
        $this->setOrderId((int)$requestParams[self::DOC_ID_PLACE]);
        $data['id'] = $this->getOrderId();

        $allOrderData = new OrderAllData($this->getOrderId(), $this->log);
        $allOrderData->allOrderData();
        $this->setLog($allOrderData->getLog());
        $data['ID_CLIENT'] = $allOrderData->getIdOrderOwner();
        $data['ID_STAGE'] = $allOrderData->getStage();
        $data['Products'] = $allOrderData->getProducts();
        $data['All_client_INFO'] = $allOrderData->getClientData();
        $data['PRICE_ORDER'] = $allOrderData->getOpportunity();
        $data['СКИДКА'] = $allOrderData->getOpportunity() / 100 * 30;
        return $data;
    }
}