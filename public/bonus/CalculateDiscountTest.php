<?php

include dirname(__DIR__) . './../vendor/autoload.php';
require_once 'CalculateDiscount.php';

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


/**
 * Тестовый класс для проверки расчёту скидки.
 *
 * Class CalculateBonusTest
 */
class CalculateDiscountTest extends TestCase
{
    protected Logger $log;

    protected function setUp(): void
    {
        $this->log = new Logger('bonus');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/test.log', Logger::DEBUG));
        $this->log->pushHandler(new FirePHPHandler());
        $this->log->pushProcessor(new MemoryUsageProcessor(true, true));
        $this->log->info('My logger is now ready');
    }

    /**
     * @dataProvider additionProvider
     */
    public function testBonusCalculate($dealValue, $rule, $oldBonus): void
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $dealValues = $moneyParser->parse((string)$dealValue, new Currency('RUB'));
        $oldBonuses = $moneyParser->parse((string)$oldBonus, new Currency('RUB'));
        $newBonus = $dealValues->divide(100);
        $newBonus = $newBonus->multiply($rule);
        $newBonus = $newBonus->add($oldBonuses);
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        $discountCalculator = new \bonus\CalculateDiscount($dealValue, $rule, $oldBonus, $this->log);

        $this->assertSame(floatval($moneyFormatter->format($newBonus)), $discountCalculator->accrualBonuses());
    }

    /**
     * @return int[][]
     */
    public function additionProvider()
    {
        return [
            [1200, 5, 200],
            [5000, 10, 1200.5],
            [2100.20, 5, 9000],
            [3250.3, 3, 12.3]
        ];
    }
}

