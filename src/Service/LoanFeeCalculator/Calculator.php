<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator;

use App\Model\Breakpoint;
use App\Model\LoanProposal;
use App\Service\LoanFeeCalculator\Contracts\DataImporter;
use App\Service\LoanFeeCalculator\Contracts\FeeCalculator;
use App\Service\LoanFeeCalculator\DataImporter\JsonImporter;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class Calculator implements FeeCalculator
{
    /** @var array<int, Breakpoint> $breakpoints */
    private array $breakpoints = [];
    public function __construct(
        DataImporter $importer = new JsonImporter(),
        mixed $dataSource = 'data.json')
    {
        $this->loadBreakpoints($importer, $dataSource);
    }

    /*
    public array $data = [
        [1000, 50],
        [2000, 90],
        [3000, 90],
        [4000, 115],
        [5000, 100],
        [6000, 120],
        [7000, 140],
        [8000, 160],
        [9000, 180],
        [10000, 200],
        [11000, 220],
        [12000, 240],
        [13000, 260],
        [14000, 280],
        [15000, 300],
        [16000, 320],
        [17000, 340],
        [18000, 360],
        [19000, 380],
        [20000, 400],
    ];
    */
    public function calculate(LoanProposal $application): float
    {
        if ($application->amount() < 1000 || $application->amount() > 20000) {
            throw new InvalidArgumentException('Value must be between 1000 and 20000');
        }

        if ($exactBreakpoint = $this->getExactBreakpoint($this->breakpoints, $application->amount())) {
            $fee = $exactBreakpoint->getFee();
        } else {
            /**
             * @var Breakpoint $minBreakpoint
             * @var Breakpoint $maxBreakpoint
             */
            [$minBreakpoint, $maxBreakpoint] = $this->getBreakpoints($this->breakpoints, $application->amount());

            // linear interpolation formula
            // y = y1 + ((x - x1) / (x2 - x1)) * (y2 - y1)
            $x = $application->amount();
            $x1 = $minBreakpoint->getAmount();
            $x2 = $maxBreakpoint->getAmount();
            $y1 = $minBreakpoint->getFee();
            $y2 = $maxBreakpoint->getFee();

            $fee = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
        }

        //fee rounded up such that fee + loan amount is an exact multiple of 5
        $totalAmount = ceil(($application->amount() + $fee) / 5) * 5;

        return round(($totalAmount - $application->amount()), 2);
    }

    private function loadBreakpoints(DataImporter $importer, mixed $source): void
    {
        $data = $importer->import($source);
        $prevAmount = $prevFee = 0;

        foreach ($data as $breakpoint) {

            if (!is_array($breakpoint)
                || count($breakpoint) > 2
                || !isset($breakpoint[0], $breakpoint[1])
                || !is_numeric($breakpoint[0])
                || !is_numeric($breakpoint[1])
            ) {
                throw new InvalidArgumentException('DataImporter provided invalid data');
            }

            $amount = $breakpoint[0];
            $fee = $breakpoint[1];

            if ($amount <= $prevAmount) {
                throw new InvalidArgumentException('Amount must be ordered ascending');
            }

            /*
             * Either there is an error in readme or fee can be smaller for bigger loan amount
             * I assumed readme data are correct
            if ($fee < $prevFee) {
                throw new InvalidArgumentException('Fee cannot be smaller than previous');
            }
            */

            $prevAmount = $breakpoint[0];
            $prevFee = $breakpoint[1];

            $this->breakpoints[] = new Breakpoint(floatval($amount), floatval($fee));
        }
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