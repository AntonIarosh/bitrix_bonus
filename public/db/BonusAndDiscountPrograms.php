<?php

declare(strict_types=1);

namespace db;

include dirname(__DIR__) . './../vendor/autoload.php';

/**
 * Class BonusAndDiscountPrograms - Задаёт и предоставляет информацию, относительно бонусов,
 * а также - по названию программы установления скидки заказа.
 *
 * @package Numbers
 */
class BonusAndDiscountPrograms
{
    private int $bonusForNewOwner;
    private int $maxOrderPrice;

    private string $rule;

    /**
     * BonusAndDiscountPrograms constructor.
     * @param int $bonusForNewOwner - количество бонусов которые получает клиент при регистрации
     * @param int $maxOrderPrice - максимальную стоимость заказа допускающую к вручению подарка
     * @param string $rule - название правила(программы) которое содержит процент скидки с заказа
     */
    public function __construct(int $bonusForNewOwner = 200, $maxOrderPrice = 4000, string $rule = 'default')
    {
        $this->bonusForNewOwner = $bonusForNewOwner;
        $this->maxOrderPrice = $maxOrderPrice;
        $this->rule = $rule;
    }

    /**
     * Получить количество бонусов которые получает клиент при регистрации
     *
     * @return int - количество бонусов
     */
    public function getBonusForNewOwner(): int
    {
        return $this->bonusForNewOwner;
    }

    /**
     * Установит количество бонусов которые получает клиент при регистрации
     *
     * @param int $bonusForNewOwner - количество бонусов
     */
    public function setBonusForNewOwner(int $bonusForNewOwner): void
    {
        $this->bonusForNewOwner = $bonusForNewOwner;
    }

    /**
     * Получить название правила(программы) которое содержит процент скидки с заказа
     *
     * @return string - название правила
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * Установить название правила(программы) которое содержит процент скидки с зак
     *
     * @param string $rule - название правила
     */
    public function setRule(string $rule): void
    {
        $this->rule = $rule;
    }

    /**
     * Получить максимальную стоимость заказа допускающую к вручению подарка
     *
     * @return int - максимальную стоимость заказа
     */
    public function getMaxOrderPrice(): int
    {
        return $this->maxOrderPrice;
    }

    /**
     * Задать максимальную стоимость заказа допускающую к вручению подарка
     *
     * @param int $maxOrderPrice - максимальную стоимость заказа
     */
    public function setMaxOrderPrice(int $maxOrderPrice): void
    {
        $this->maxOrderPrice = $maxOrderPrice;
    }
}