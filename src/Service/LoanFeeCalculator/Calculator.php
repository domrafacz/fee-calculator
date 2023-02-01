<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator;

use App\Model\Breakpoint;
use App\Model\LoanProposal;
use App\Service\LoanFeeCalculator\Contracts\DataImporter;
use App\Service\LoanFeeCalculator\Contracts\FeeCalculator;
use App\Service\LoanFeeCalculator\Contracts\FeeCalculatorStrategy;
use App\Service\LoanFeeCalculator\DataImporter\JsonImporter;
use App\Service\LoanFeeCalculator\Strategy\LinearInterpolation;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class Calculator implements FeeCalculator
{
    /** @var array<int, Breakpoint> $breakpoints */
    private array $breakpoints = [];
    public function __construct(
        DataImporter                           $importer = new JsonImporter(),
        mixed                                  $dataSource = 'data.json',
        private readonly FeeCalculatorStrategy $strategy = new LinearInterpolation(),
    ) {
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

        return $this->strategy->calculate($application->amount(), $this->breakpoints);
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
}
