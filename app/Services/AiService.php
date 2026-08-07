<?php

namespace App\Services;

use App\AI\Providers\ProviderInterface;
use App\Jobs\GenerateFormJob;
use App\Models\AiJob;
use App\Models\Form;
use App\Support\AiPromptBuilder;
use App\Support\SchemaValidator;
use RuntimeException;

class AiService
{
    public function __construct(protected FormService $forms) {}

    public function dispatchGeneration(Form $form, string $description): AiJob
    {
        $job = AiJob::create([
            'form_id' => $form->id,
            'prompt' => AiPromptBuilder::generate($description, $form->title),
            'model' => config('services.ai.model'),
            'status' => 'pending',
        ]);

        GenerateFormJob::dispatch($job);

        return $job;
    }

    public function process(AiJob $job): void
    {
        $form = $job->form;

        if (! $form) {
            throw new RuntimeException('AI job has no associated form.');
        }

        $model = $job->model ?? config('services.ai.model');

        $raw = $this->provider()->complete($job->prompt, $model);

        $schema = SchemaValidator::parseAndRepair($raw);
        $schema = SchemaValidator::validate($schema, $form->title);

        $this->forms->autosave($form, ['schema' => $schema]);

        $job->update([
            'status' => 'completed',
            'response' => $raw,
            'model' => $model,
        ]);
    }

    protected function provider(): ProviderInterface
    {
        $class = config('services.ai.provider');

        if (! is_string($class) || ! is_a($class, ProviderInterface::class, true)) {
            throw new RuntimeException('services.ai.provider must be a class implementing ProviderInterface.');
        }

        return app($class);
    }
}
