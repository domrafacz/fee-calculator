<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator\Contracts;

interface DataImporter
{
    /** @return array<int, array<int, float>> */
    public function import(mixed $source): array;
}