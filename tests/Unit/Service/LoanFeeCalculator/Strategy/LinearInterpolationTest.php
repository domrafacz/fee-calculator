<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\LoanFeeCalculator\Strategy;

use App\Model\Breakpoint;
use App\Service\LoanFeeCalculator\Strategy\LinearInterpolation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class LinearInterpolationTest extends TestCase
{
    /** @var array<int, Breakpoint> $breakpoints */
    private array $breakpoints = [];

    protected function tearDown(): void
    {
        $this->breakpoints = [];
    }

    public function testMinBreakpointNotFound(): void
    {
        $this->breakpoints[] = new Breakpoint(21000, 100);
        $this->breakpoints[] = new Breakpoint(22000, 200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot get min breakpoint for loan amount: 10000.000000');

        $strategy = new LinearInterpolation();
        $strategy->calculate(10000, $this->breakpoints);
    }

    public function testMaxBreakpointNotFound(): void
    {
        $this->breakpoints[] = new Breakpoint(8000, 100);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot get max breakpoint for loan amount: 10000.000000');

        $strategy = new LinearInterpolation();
        $strategy->calculate(10000, $this->breakpoints);
    }
}
