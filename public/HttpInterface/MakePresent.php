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
 * Class MakePresent - Вычисление возможности прикрепление подарка, и его прикрепление
 *
 * @package Numbers
 */
class MakePresent
{
    private int $orderId;
    private int $idOwner;
    private $presents;

    private Logger $log;

    /**
     * OrderAllData constructor - Конструктор класса
     *
     * @param int $orderId - идентификатор заказа
     * @param $idOvner - идентификатор заказчика
     * @param $log - лог
     */
    public function __construct(int $orderId, int $idOvner, $log)
    {
        $this->orderId = $orderId;
        $this->idOwner = $idOvner;
        $this->log = $log;
    }

    /**
     * Получить подарки
     *
     * @return mixed - подарки, в ввиде массива продуктов
     */
    public function getPresents()
    {
        return $this->presents;
    }

    /**
     * Задать подраки
     *
     * @param mixed $presents - подарки, в ввиде массива продуктов
     */
    public function setPresents($presents): void
    {
        $this->presents = $presents;
    }

    /**
     * Задать идентификатор заказа
     *
     * @param $id - идентификатор заказа
     */
    public function setOrderId($id)
    {
        $this->orderId = $id;
    }

    /**
     * Получить идентификатор заказа
     *
     * @return int - идентификатор заказа
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Задать идентификатор заказчика
     *
     * @param $idOwner - идентификатор заказчика
     */
    public function setIdOrderOwner($idOwner)
    {
        $this->idOwner = $idOwner;
    }

    /**
     * Получить идентификатор заказчика
     *
     * @return mixed - идентификатор заказчика
     */
    public function getIdOrderOwner(): int
    {
        return $this->idOwner;
    }


    /**
     * Обнуружение подарков среди товаров, и их сбор в массив
     *
     * @return array - все подарки
     */
    public function calculatePresents(): array
    {
        try {
            $core = (new CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-r1mql2.bitrix24.ru/rest/1/yn57uv4t4npz440h/')
                ->build();

            // Запрос всех продуктов
            $res = $core->call('crm.product.list', ['order' => ['NAME' => 'ASC'], 'select' => ['*', 'PROPERTY_*']]);
            $arrayOrderData = $res->getResponseData()->getResult()->getResultData();

            $presents = [];
            // Выборка продуктов - подарков
            foreach ($arrayOrderData as $value) {
                if ($value['PROPERTY_109'] != null) {
                    $value['PRICE'] = 0;
                    array_push($presents, $value);
                }
            }

            $this->setPresents($presents);

            return $presents;
        } catch (Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }

    /**
     * Выбор одного подарка, его прикрепление к табличной части заказа
     *
     * @param $allpresents - массив со всеми подарками
     */
    public function makePresents($allpresents)
    {
        try {
            $core = (new CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-r1mql2.bitrix24.ru/rest/1/yn57uv4t4npz440h/')
                ->build();

            // Выбор конкретного подарка перечня подарков.
            $randomIterator = random_int(1, count($allpresents));
            $this->log->debug(
                'Массив - ',
                [
                    'Id выбранного подарка' => $randomIterator,
                    'Всего подарков' => count($allpresents),
                ]
            );
            // Запрос табличной части заказа
            $res = $core->call('crm.deal.productrows.get', ['ID' => $this->orderId]);
            // Формирование записи - строки подарка в табличной части
            $oldTablePart = $res->getResponseData()->getResult()->getResultData();
            $thisPresent = [];
            $thisPresent['PRODUCT_ID'] = $allpresents[$randomIterator - 1]['ID'];
            $thisPresent['PRICE'] = 0;
            $thisPresent['QUANTITY'] = 1;
            $this->log->debug(
                'Подарок - ',
                [
                    'Подарок ' => $thisPresent,
                ]
            );

            array_push($oldTablePart, $thisPresent);
            // Выполнение записи табличной части заказа в битрикс
            $res = $core->call('crm.deal.productrows.set', ['ID' => $this->getOrderId(), 'ROWS' => $oldTablePart]);
        } catch (Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }
}