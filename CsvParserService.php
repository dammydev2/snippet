<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use RuntimeException;

class CsvParserService
{
    /**
     * Parse CSV file and return as collection of associative arrays
     */
    public function parse(string $filePath): Collection
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("CSV file not found: {$filePath}");
        }

        $rows   = array_map('str_getcsv', file($filePath));
        $header = array_shift($rows);

        if (empty($header)) {
            throw new RuntimeException('CSV file is empty or has no header row.');
        }

        return collect($rows)
            ->filter(fn($row) => count($row) === count($header))
            ->map(fn($row) => array_combine($header, $row));
    }
}