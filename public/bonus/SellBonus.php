<?php

declare(strict_types=1);

namespace bonus;

include dirname(__DIR__) . './../vendor/autoload.php';

use Bitrix24\SDK\Core\CoreBuilder;
use Exception;
use Monolog\Logger;
use Throwable;

/**
 * Class CalculateBonus - выполняют расчёт и выполнение скидки.
 * @package Numbers
 */
class SellBonus
{

    private int $orderId;
    private $newTablePart;
    private Logger $log;

    /**
     * Логгер получить
     *
     * @return Logger
     */
    public function getLog(): Logger
    {
        return $this->log;
    }

    /**
     * Логгер установить
     * @param Logger $log
     */
    public function setLog(Logger $log): void
    {
        $this->log = $log;
    }


    /**
     * OrderAllData constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     * @throws Exception - исключение
     */
    public function __construct(int $orderId, $newTablePart, $log)
    {
        $this->orderId = $orderId;
        $this->newTablePart = $newTablePart;
        $this->log = $log;
    }

    /**
     * Получить табличную часть заказа после расчёта скидки
     *
     * @return mixed
     */
    public function getNewTablePart()
    {
        return $this->newTablePart;
    }

    /**
     * Задать табличную часть заказа после расчёта скидки
     *
     * @param mixed $newTablePart
     */
    public function setNewTablePart($newTablePart): void
    {
        $this->newTablePart = $newTablePart;
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
     * Выполняет расчёт бонусной суммы и записывает бонусы в битрикс
     * @return void
     */
    public function makeSellBonuses()
    {
        try {
            $core = (new CoreBuilder())
                ->withLogger($this->log)
                ->withWebhookUrl('https://b24-r1mql2.bitrix24.ru/rest/1/yn57uv4t4npz440h/')
                ->build();

            // Выполнение записи табличной части заказа в битрикс
            $tablePart = $this->getNewTablePart();
            $res = $core->call('crm.deal.productrows.set', ['ID' => $this->getOrderId(), 'ROWS' => $tablePart]);
            $this->log->debug(
                'Битрикс - ',
                [
                    'в Битрикс данные записаны - ' => $res,
                ]
            );
        } catch (Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('ошибка: %s', $exception->getLine()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
        }
    }
}