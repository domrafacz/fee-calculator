<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator\Contracts;

use App\Model\Breakpoint;

interface FeeCalculatorStrategy
{
    /**
     * @return float The calculated total fee.
     * @param array<int, Breakpoint> $breakpoints
     */
    public function calculate(float $loanAmount, array $breakpoints): float;
}
