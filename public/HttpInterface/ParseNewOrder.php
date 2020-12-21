<?php

declare(strict_types=1);

namespace HttpInterface;

include dirname(__DIR__) . './../vendor/autoload.php';

use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class ParseNewOrder - Обрабатывает сработавший вэбхук и
 * получается идентификатор заказа
 * @package Numbers
 */
class ParseNewOrder
{
    private $orderId;
    private $orderData;

    const DOC_ID_PLACE = 1;
    const DOC_ID_PLACE_IN_ALL_IFO = 2;

    /**
     * ParseNewOrder constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     * @param array $orderData - данные заказа
     */
    function __construct($orderData = 0, $orderId = 0)
    {
        $this->$orderId = $orderId;
        $this->$orderData = $orderData;
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
    public function getOrdrId()
    {
        return $this->orderId;
    }
    /**
     * Задать данные заказа
     * @param $orderData - данные заказа
     */
    public function setOrderData($orderData)
    {
        $this->$orderData = $orderData;
    }

    /**
     * Получить данные заказа
     * @return - данные заказа
     */
    public function getOrderData()
    {
        return $this->orderData;
    }

    /**
     * Получить из запроса данные и разобрать их
     * @return array - данные заказа
     */
    public function makeOrderData()
    {
        $request = Request::createFromGlobals();
        $data = [];
        $data["DocId"]  = $request->get("document_id");
        //$data["ContentALL"]  = $request->request->all();

        $requestParams = explode("_",$data["DocId"][self::DOC_ID_PLACE_IN_ALL_IFO]);
        $this->setOrderId($requestParams[self::DOC_ID_PLACE]);

        //$data["id"] = $data["DocId"][self::DOC_ID_PLACE_IN_ALL_IFO];
        $data["id"] = $this->getOrdrId();
        //$data = $_REQUEST;
        /*var_dump($request->getQueryString());
        $requestParams = explode("_",$data["DocId"][2]);
        $data=[];
        foreach ($requestParams as $value) {
            $temp = explode("=", $value);
            $data[$temp[0]] = $temp[1];
        }*/
        print_r($data);
        return $data;
    }
}