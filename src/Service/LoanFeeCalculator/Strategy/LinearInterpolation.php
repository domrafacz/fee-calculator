<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator\Strategy;

use App\Model\Breakpoint;
use App\Service\LoanFeeCalculator\Contracts\FeeCalculatorStrategy;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class LinearInterpolation implements FeeCalculatorStrategy
{

    public function calculate(float $loanAmount, array $breakpoints): float
    {
        if ($exactBreakpoint = $this->getExactBreakpoint($breakpoints, $loanAmount)) {
            $fee = $exactBreakpoint->getFee();
        } else {
            /**
             * @var Breakpoint $minBreakpoint
             * @var Breakpoint $maxBreakpoint
             */
            [$minBreakpoint, $maxBreakpoint] = $this->getBreakpoints($breakpoints, $loanAmount);

            // linear interpolation formula
            // y = y1 + ((x - x1) / (x2 - x1)) * (y2 - y1)
            $x = $loanAmount;
            $x1 = $minBreakpoint->getAmount();
            $x2 = $maxBreakpoint->getAmount();
            $y1 = $minBreakpoint->getFee();
            $y2 = $maxBreakpoint->getFee();

            $fee = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
        }

        //fee rounded up such that fee + loan amount is an exact multiple of 5
        $totalAmount = ceil(($loanAmount + $fee) / 5) * 5;

        return round(($totalAmount - $loanAmount), 2);
    }

    /** @param array<int, Breakpoint> $breakpoints */
    private function getExactBreakpoint(array $breakpoints, float $amount): ?Breakpoint
    {
        /** @var Breakpoint $breakpoint */
        foreach ($breakpoints as $breakpoint) {
            if ($breakpoint->getAmount() === $amount) {
                return $breakpoint;
            }
        }

        return null;
    }

    /**
     * @param array<int, Breakpoint> $breakpoints
     * @return array<int, Breakpoint>
     */
    private function getBreakpoints(array $breakpoints, float $loanAmount): array
    {
        $minBreakpoint = $maxBreakpoint = $minBreakpointIndex = null;

        /** @var Breakpoint $breakpoint */
        foreach ($breakpoints as $index => $breakpoint) {

            //find min breakpoint
            if ($breakpoint->getAmount() < $loanAmount) {
                $minBreakpoint = $breakpoint;
                $minBreakpointIndex = $index;
            }
        }

        if ($minBreakpointIndex === null || !isset($breakpoints[$minBreakpointIndex+1])) {
            throw new InvalidArgumentException(sprintf('Cannot get max breakpoint for loan amount: %f', $loanAmount));
        } else {
            $maxBreakpoint = $breakpoints[$minBreakpointIndex+1];
        }

        if (!$minBreakpoint instanceof Breakpoint || !$maxBreakpoint instanceof Breakpoint) {
            throw new InvalidArgumentException(sprintf('Cannot get breakpoints for loan amount: %f', $loanAmount));
        }

        return [$minBreakpoint, $maxBreakpoint];
    }
}