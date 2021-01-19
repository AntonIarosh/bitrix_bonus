<?php

declare(strict_types=1);

namespace bonus;

include dirname(__DIR__) . './../vendor/autoload.php';

use Bitrix24\SDK\Core\CoreBuilder;
use Exception;
use Money\Currencies\ISOCurrencies;
use Monolog\Logger;
use Money\Money;
use Monolog\Processor\MemoryUsageProcessor;
use Symfony\Component\HttpClient\HttpClient;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Throwable;


use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;


/**
 * Class CalculateBonus - выполняют расчёт и выполнение скидки.
 * @package Numbers
 */
class CalculateDiscount
{
    private $dealValue;
    private $rule;
    private $oldBonuses;

    private Logger $log;

    /**
     * CalculateDiscount constructor - Конструктор класса
     * @param $dealValue - стоимость всего заказа
     * @param $rule - правило - процент от стоимости заказа
     * @param $oldBonuses - количество имеющихся бонусов
     * @param $log - лог
     */
    public function __construct($dealValue, $rule, $oldBonuses, $log)
    {
        $this->dealValue = $dealValue;
        $this->rule = $rule;
        $this->oldBonuses = $oldBonuses;
        $this->log = $log;
    }

    /**
     *  Получить стоимость сделки
     *
     * @return mixed - стоимость сделки
     */
    public function getDealValue()
    {
        return $this->dealValue;
    }

    /**
     * Задать стоимость сделки
     *
     * @param mixed $dealValue - стоимость сделки
     */
    public function setDealValue($dealValue): void
    {
        $this->dealValue = $dealValue;
    }

    /**
     * Получить правило начисления скидки
     *
     * @return mixed - процент от суммы сделки
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     *  Задать правило начисления скидки
     *
     * @param mixed $rule - процент от суммы сделки
     */
    public function setRule($rule): void
    {
        $this->rule = $rule;
    }

    /**
     * Получить количество имеющихся у пользователя бонусов
     *
     * @return mixed - количество имеющихся у пользователя бонусов
     */
    public function getOldBonuses()
    {
        return $this->oldBonuses;
    }

    /**
     * Задать количество имеющихся у пользователя бонусов
     *
     * @param mixed $oldBonuses - количество имеющихся у пользователя бонусов
     */
    public function setOldBonuses($oldBonuses): void
    {
        $this->oldBonuses = $oldBonuses;
    }

    /**
     * Получить лог
     *
     * @return Logger
     */
    public function getLog(): Logger
    {
        return $this->log;
    }

    /**
     * Задать лог
     *
     * @param Logger $log
     */
    public function setLog(Logger $log): void
    {
        $this->log = $log;
    }

    /**
     * Выполняет расчёт скидочной суммы
     * @return - скидочной сумму
     */
    public function accrualBonuses()
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $newBonuses = $moneyParser->parse((string)$this->getDealValue(), new Currency('RUB'));
        $oldBonuses = $moneyParser->parse((string)$this->getOldBonuses(), new Currency('RUB'));
        $newBonuses = $newBonuses->divide(100);
        $newBonuses = $newBonuses->multiply($this->getRule());
        $newBonuses = $newBonuses->add($oldBonuses);

        $moneyFormatter = new DecimalMoneyFormatter($currencies);
        return floatval($moneyFormatter->format($newBonuses));
    }
}