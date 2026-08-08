<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessDocxImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public int $timeout = 120;

    public function __construct(public Import $import) {}

    public function handle(ImportService $service): void
    {
        $service->processDocs($this->import);
    }

    public function failed(?Throwable $e): void
    {
        $this->import->update([
            'status' => 'failed',
            'error_message' => $e?->getMessage(),
        ]);
    }
}
