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
     * Задать данные заказа
     * @param $orderData - данные заказа
     */
    function setOrderData($orderData)
    {
        $this->$orderData = $orderData;
    }

    function getOrderData()
    {
        return $this->orderData;
    }

    function makeOrderData()
    {
        $request = Request::createFromGlobals();
        $data = [];
        $data["DocId"]  = $request->get("document_id");
        //$data["ContentALL"]  = $request->request->all();
        $data["id"] = $data["DocId"][2];
        //$data = $_REQUEST;
        /*var_dump($request->getQueryString());
        $requestParams = explode("&",$request->getQueryString());
        $data=[];
        foreach ($requestParams as $value) {
            $temp = explode("=", $value);
            $data[$temp[0]] = $temp[1];
        }*/
        print_r($data);
        return $data;
    }
}