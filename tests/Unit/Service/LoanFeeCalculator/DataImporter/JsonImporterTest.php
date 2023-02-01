<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\LoanFeeCalculator\DataImporter;

use App\Service\LoanFeeCalculator\DataImporter\JsonImporter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class JsonImporterTest extends TestCase
{
    public function testInvalidSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filename must be a string, integer provided');

        $importer = new JsonImporter();
        $importer->import(10);
    }

    public function testInvalidFilePath(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File not found: invalid.json');

        $importer = new JsonImporter();
        $importer->import('invalid.json');
    }

    public function testInvalidJsonFileContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source data contains invalid json');

        $importer = new JsonImporter();
        $importer->import('data_invalid_json.json');
    }
}
