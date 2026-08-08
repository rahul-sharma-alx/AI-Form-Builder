<?php

namespace App\Jobs;

use App\Models\AiJob;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class EditSchemaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public int $timeout = 180;

    public function __construct(public AiJob $aiJob) {}

    public function handle(AiService $ai): void
    {
        $this->aiJob->update(['status' => 'processing']);

        $ai->processEdit($this->aiJob);
    }

    public function failed(?Throwable $e): void
    {
        $this->aiJob->update([
            'status' => 'failed',
            'error_message' => $e?->getMessage(),
        ]);
    }
}
