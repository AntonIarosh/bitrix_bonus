<?php

declare(strict_types=1);

namespace HttpInterface;

include dirname(__DIR__) . './../vendor/autoload.php';

use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Monolog\Handler\StreamHandler;

/**
 * Class MakePresent - Вычисление возможности прикрепление подарка, и его прикрепление
 * @package Numbers
 */
class MakePresent
{
    private $orderId;
    private $idOvner;
    private $opportunity;
    private $presents;

    private $log;

    /**
     * OrderAllData constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     */
    public function __construct($orderId, $opportunity, $idOvner)
    {
        $this->orderId = $orderId;
        $this->opportunity = $opportunity;
        $this->idOvner = $idOvner;
        $this->log = new Logger('Present');
        $this->log->pushHandler(new StreamHandler('logs/present.log', Logger::DEBUG));
        $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor(true, true));

        $this->client = HttpClient::create(['http_version' => '2.0']);
    }
    /**
     * @return mixed
     */
    public function getPresents()
    {
        return $this->presents;
    }

    /**
     * @param mixed $presents
     */
    public function setPresents($presents): void
    {
        $this->presents = $presents;
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
    public function calculatePresents()
    {
        try {
            $core = (new \Bitrix24\SDK\Core\CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-r1mql2.bitrix24.ru/rest/1/yn57uv4t4npz440h/')
                ->build();

            // Запрос всех продуктов
            $res = $core->call('crm.product.list',['order'=> ["NAME" => "ASC"] , 'select' => ['*', 'PROPERTY_*']]);
            $arrayOrderData = $res->getResponseData()->getResult()->getResultData();

            $presents = [];
            // Выборка продуктов - подарков
            foreach ($arrayOrderData as $value) {
                if ($value['PROPERTY_109'] != null) {
                    $value['PRICE'] = 0;
                    array_push($presents,$value);
                }

            }
            $this->log->debug('[v] - ',
                              [
                                  '[v] ' => $presents,
                              ]);
            $this->setPresents($presents);

            return $presents;

        } catch (\Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }

    /**
     * @param $allpresents
     */
    public function makePresents($allpresents){
        try {
            $core = (new \Bitrix24\SDK\Core\CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-r1mql2.bitrix24.ru/rest/1/yn57uv4t4npz440h/')
                ->build();

            // Выбор конкретного подарка перечня подарков.
            $randomIterator = random_int(1, count($allpresents));
            $this->log->debug('Массив - ',
                              [
                                  'Id выбранного подарка' => $randomIterator,
                                  'Всего подарков' => count($allpresents),
                              ]);
            // Запрос табличной части заказа
            $res = $core->call('crm.deal.productrows.get',['ID' => $this->orderId]);
            // Формирование записи - строки подарка в табличной части
            $oldTablePart = $res->getResponseData()->getResult()->getResultData();
            $thisPresent =[];
            $thisPresent['PRODUCT_ID'] = $allpresents[$randomIterator - 1]['ID'];
            $thisPresent['PRICE'] = 0;
            $thisPresent['QUANTITY'] = 1;
            $this->log->debug('Подарок - ',
                              [
                                  'Подарок ' => $thisPresent,
                              ]);

            array_push($oldTablePart, $thisPresent);
            // Выполнение записи табличной части заказа в битрикс
            $res = $core->call('crm.deal.productrows.set',['ID'=> $this->getOrderId(), 'ROWS'=> $oldTablePart]);

        } catch (\Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }
}