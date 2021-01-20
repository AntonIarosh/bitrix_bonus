<?php

declare(strict_types=1);

namespace bonus;

include dirname(__DIR__) . './../vendor/autoload.php';

use Money\Currencies\ISOCurrencies;
use Monolog\Logger;
use Throwable;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;


/**
 * Class CalculateBonus - выполняют расчёт и выполнение скидки.
 * @package Numbers
 */
class CalculateBonus
{
    private int $idOwner;
    private $products;
    private $opportunity;
    private int $orderId;
    private float $bonusValue;
    private $newTablePart;

    private int $discountPercentage;

    private Logger $log;

    /**
     * CalculateBonus constructor - Конструктор класса
     * @param int $orderId - идентификатор заказа
     * @param $idOwner - идентификатор заказчика
     * @param $products - табличная часть сделки
     * @param $opportunity - дсумма всей сделки
     * @param $bonusValue - количество имеющихся бонусов
     * @param $discountPercentage - процент максимальной скидки
     * @param $log - лог
     */
    public function __construct(
        int $orderId,
        int $idOwner,
        $products,
        $opportunity,
        float $bonusValue,
        $discountPercentage,
        $log
    ) {
        $this->orderId = $orderId;
        $this->idOwner = $idOwner;
        $this->products = $products;
        $this->opportunity = $opportunity;
        $this->bonusValue = $bonusValue;
        $this->discountPercentage = $discountPercentage;
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
     * Получить процент максимальной скидки
     * @return int - процент максимальной скидки
     */
    public function getDiscountPercentage(): int
    {
        return $this->discountPercentage;
    }

    /**
     * Установить процент максимальной скидки
     * @param $discountPercentage - процент максимальной скидки
     */
    public function setDiscountPercentage($discountPercentage)
    {
        $this->discountPercentage = $discountPercentage;
    }

    /**
     * Задать количество бонусов.
     * @param $bonusValue - стадия заказа
     */
    public function setBonus($bonusValue)
    {
        $this->bonusValue = $bonusValue;
    }

    /**
     * Получить количество бонусов
     * @return float - бонусы клиента
     */
    public function getBonus(): float
    {
        return $this->bonusValue;
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
     * @return int - идентификатор заказчика
     */
    public function getIdOrderOwner(): int
    {
        return $this->idOwner;
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
     * @return - сумму сделки
     */
    public function getOpportunity()
    {
        return $this->opportunity;
    }


    /**
     * Выполняет расчёт бонусной суммы и записывает бонусы в битрикс
     * @return float|int|string - остаток бонусов
     */
    public function calculateAndDiscount()
    {
        try {
            $this->log->debug(
                'Бонусы были - ',
                [
                    'Бонусы были - ' => $this->getBonus(),
                ]
            );

            $currencies = new ISOCurrencies();
            $moneyParser = new DecimalMoneyParser($currencies);
            $remains = $moneyParser->parse('0', new Currency('RUB'));
            $allDiscount = $moneyParser->parse('0', new Currency('RUB'));
            $bonusMoney = $moneyParser->parse((string)$this->getBonus(), new Currency('RUB'));
            $discount_1 = $this->getOpportunity() / 100 * $this->getDiscountPercentage();
            $this->log->debug(
                'Простая скидка - ',
                [
                    'Простая скидка  - ' => $discount_1,
                ]
            );

            // Создаём объект Money\Currency - для вычисления скидки на весь заказ
            $moneyOrder = $moneyParser->parse((string)$this->getOpportunity(), new Currency('RUB'));
            $discount = $moneyOrder->divide(100);
            $discount = $discount->multiply($this->getDiscountPercentage());
            $moneyFormatter = new DecimalMoneyFormatter($currencies);
            $this->log->debug(
                'Сложная скидка - ',
                [
                    'Сложная скидка  - ' => $moneyFormatter->format($discount),
                ]
            );
            if (floatval($moneyFormatter->format($discount)) < $this->getBonus()) {
                $remains = $bonusMoney->subtract($discount);
                $allDiscount = $discount;
            } else {
                $allDiscount = $bonusMoney;
            }
            $productsInfo = [];
            $this->log->debug(
                'Остатки бонусной суммы - ',
                [
                    'Остатки бонусной суммы - ' => $moneyFormatter->format($remains),
                ]
            );
            $this->log->debug(
                'Сумма скидки - ',
                [
                    'Сумма скидки - ' => $moneyFormatter->format($allDiscount),
                ]
            );
            $this->log->debug(
                'Исходные данные - ',
                [
                    'Исходные данные - ' => $this->getProducts(),
                ]
            );

            $discountNumber = $moneyParser->parse('0', new Currency('RUB'));
            $tablePart = [];
            $oldTablePart = $this->getProducts();
            for ($i = 0; $i < count($oldTablePart); $i++) {
                $moneyPriceForOneStringOfTablePart = $moneyParser->parse(
                    (string)$oldTablePart[$i]['PRICE'],
                    new Currency('RUB')
                );
                $moneyPriceForOneStringOfTablePartMulQuantity = $moneyPriceForOneStringOfTablePart->multiply(
                    $oldTablePart[$i]['QUANTITY']
                );
                // Попытка записать всю скидку в текущию позицию
                if (floatval($moneyFormatter->format($allDiscount)) < floatval(
                        $moneyFormatter->format($moneyPriceForOneStringOfTablePartMulQuantity)
                    )) {
                    $divisionBonusForQuantity = $allDiscount->divide($oldTablePart[$i]['QUANTITY']);

                    $oldTablePart[$i]['DISCOUNT_SUM'] = $moneyFormatter->format($divisionBonusForQuantity);

                    $oldTablePart[$i]['PRICE_EXCLUSIVE'] = $moneyFormatter->format(
                        $moneyPriceForOneStringOfTablePart->subtract($divisionBonusForQuantity)
                    );

                    $allDiscount = $allDiscount->subtract(
                        $divisionBonusForQuantity->multiply($oldTablePart[$i]['QUANTITY'])
                    );

                    $discountNumber = $discountNumber->add(
                        $divisionBonusForQuantity->multiply($oldTablePart[$i]['QUANTITY'])
                    );
                    $tablePart[] = $oldTablePart[$i];
                    continue;
                }


                // Попытка делить скидку на все позиции
                $discountForOnePosition = $allDiscount->divide(count($oldTablePart) - $i);
                $this->log->debug(
                    'скидка на эту строку тч - ',
                    [
                        'скидка на эту строку тч - ' => $moneyFormatter->format($discountForOnePosition),
                        'позиций в  тч - ' => count($oldTablePart),
                        'позиция - ' => count($oldTablePart) - $i,
                    ]
                );
                $pOld = $moneyParser->parse((string)$oldTablePart[$i]['PRICE'], new Currency('RUB'));
                //Если стоимость какждого товара табличной части меньше чем сумма скидки на эту позицию табличной части
                if (floatval($moneyFormatter->format($pOld)) < floatval(
                        $moneyFormatter->format($discountForOnePosition)
                    )) {
                    $this->log->debug(
                        'цена товара в тч меньше скидки - ',
                        [
                            'цена товара - ' => $moneyFormatter->format($pOld),
                            'скидка - ' => $moneyFormatter->format($discountForOnePosition),
                        ]
                    );
                    $divForQuantity = $pOld->divide($oldTablePart[$i]['QUANTITY']);
                    $divForPercents = $divForQuantity->divide(100);
                    $discountSum = $divForPercents->multiply(95);

                    $oldTablePart[$i]['DISCOUNT_SUM'] = $moneyFormatter->format($discountSum);
                    $this->log->debug(
                        'Простая сумма скидки - ',
                        [
                            'Простая сумма скидки - ' => $oldTablePart[$i]['DISCOUNT_SUM'],
                        ]
                    );

                    $oldTablePart[$i]['PRICE_EXCLUSIVE'] = $moneyFormatter->format($pOld->subtract($discountSum));

                    $discountNumber = $discountNumber->add($discountSum);
                    $allDiscount = $allDiscount->subtract($discountSum->multiply($oldTablePart[$i]['QUANTITY']));

                    $this->log->debug(
                        'Сумма всей скидки - ',
                        [
                            'после вычитания позиции тч - ' => $moneyFormatter->format($allDiscount),
                        ]
                    );
                    if ($i == count($oldTablePart) - 1) {
                        $remains = $remains->add($allDiscount);
                        $this->log->debug(
                            'Остатки бонусной суммы - ',
                            [
                                'Остатки бонусной суммы - ' => $moneyFormatter->format($remains),
                            ]
                        );
                    }
                    //Если стоимость какждого товара табличной части больше чем сумма скидки на эту позицию табличной части
                } else {
                    $this->log->debug(
                        'цена товара в тч больше скидки - ',
                        [
                            'цена товара - ' => $moneyFormatter->format($pOld),
                            'скидка - ' => $moneyFormatter->format($discountForOnePosition),
                        ]
                    );

                    $devDiscountSum = $discountForOnePosition->divide($oldTablePart[$i]['QUANTITY']);
                    $oldTablePart[$i]['DISCOUNT_SUM'] = $moneyFormatter->format($devDiscountSum);

                    $oldTablePart[$i]['PRICE_EXCLUSIVE'] = $moneyFormatter->format($pOld->subtract($devDiscountSum));

                    $discountNumber = $discountNumber->add($devDiscountSum);

                    $allDiscount = $allDiscount->subtract($devDiscountSum->multiply($oldTablePart[$i]['QUANTITY']));
                    $this->log->debug(
                        'Сумма всей скидки - ',
                        [
                            'после вычитания позиции тч - ' => $moneyFormatter->format($allDiscount),
                        ]
                    );

                    $this->log->debug(
                        'Простая сумма скидки - ',
                        [
                            'Простая сумма скидки - ' => $oldTablePart[$i]['DISCOUNT_SUM'],
                        ]
                    );
                }
                $tablePart[] = $oldTablePart[$i];
            }
            $this->log->debug(
                'Остаток скидки этой покупки - ',
                ['Остаток скидки этой покупки - ' => $moneyFormatter->format($allDiscount),]
            );
            $this->log->debug(
                'Скидка произведена на - ',
                ['Скидка произведена на' => $moneyFormatter->format($discountNumber),]
            );
            $this->log->debug(
                'Массив для записи - ',
                ['Массив для записи - ' => $tablePart,]
            );
            $this->setNewTablePart($tablePart);

            $this->log->debug(
                'Бонусы стали - ',
                ['Бонусы стали - ' => $moneyFormatter->format($remains),]
            );
            $this->log->debug(
                'Скидка остаток - ',
                ['Скидка - остаток - ' => $moneyFormatter->format($allDiscount),]
            );
            return floatval($moneyFormatter->format($remains));
        } catch (Throwable $exception) {
            print(sprintf('ошибка: %s', $exception->getMessage()) . PHP_EOL);
            print(sprintf('ошибка: %s', $exception->getLine()) . PHP_EOL);
            print(sprintf('тип: %s', get_class($exception)) . PHP_EOL);
            print(sprintf('trace: %s', $exception->getTraceAsString()) . PHP_EOL);
            return $exception->getMessage();
        }
    }
}