<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\LoanFeeCalculator;

use App\Model\LoanProposal;
use App\Service\LoanFeeCalculator\Calculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class CalculatorTest extends TestCase
{
    public function testLinearStrategyExactAmount(): void
    {
        $calculator = new Calculator();

        $this->assertEquals(100, $calculator->calculate(new LoanProposal(5000)));
    }

    public function testLinearStrategyFallBetweenBreakpoints(): void
    {
        $calculator = new Calculator();

        $this->assertEquals(130, $calculator->calculate(new LoanProposal(6500)));
        $this->assertEquals(385, $calculator->calculate(new LoanProposal(19250)));
    }

    public function testLoanAmountOutOfRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be between 1000 and 20000');

        $calculator = new Calculator();
        $calculator->calculate(new LoanProposal(900));
    }

    public function testInvalidBreakpointsDataOrder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be ordered ascending');

        new Calculator(dataSource: 'data_invalid_order.json');
    }

    public function testInvalidBreakpointsData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DataImporter provided invalid data');

        new Calculator(dataSource: 'data_invalid_amount_type.json');
    }
}
