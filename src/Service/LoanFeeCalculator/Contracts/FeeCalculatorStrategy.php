<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator\Contracts;

interface FeeCalculatorStrategy
{
    /**
     * @return float The calculated total fee.
     */
    public function calculate(float $loanAmount, array $breakpoints): float;
}
