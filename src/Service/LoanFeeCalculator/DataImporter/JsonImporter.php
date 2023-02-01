<?php

declare(strict_types=1);

namespace App\Service\LoanFeeCalculator\DataImporter;

use App\Service\LoanFeeCalculator\Contracts\DataImporter;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class JsonImporter implements DataImporter
{
    public function import(mixed $source): array
    {
        if (!is_string($source)) {
            throw new InvalidArgumentException(sprintf('Filename must be a string, %s provided', gettype($source)));
        }

        $fileData = file_get_contents($source);

        if ($fileData === false) {
            throw new FileNotFoundException(sprintf('File not found: %s', $source));
        }

        $jsonData = json_decode($fileData);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($jsonData)) {
            throw new InvalidArgumentException('Source data contains invalid json');
        }

        return $jsonData;
    }
}
