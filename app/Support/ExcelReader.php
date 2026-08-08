<?php

namespace App\Support;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Facades\Excel;

class ExcelReader
{
    public static function preview(string $path, int $limit = 15): array
    {
        $reader = new class($limit) implements ToArray, WithLimit, WithChunkReading
        {
            public array $rows = [];

            public function __construct(public int $limit) {}

            public function limit(): int
            {
                return $this->limit;
            }

            public function chunkSize(): int
            {
                return 1000;
            }

            public function array(array $array): void
            {
                $this->rows = array_merge($this->rows, $array);
            }
        };

        Excel::import($reader, $path);

        return $reader->rows;
    }

    public static function rows(string $path): array
    {
        $reader = new class implements ToArray
        {
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = $array;
            }
        };

        Excel::import($reader, $path);

        return $reader->rows;
    }
}
