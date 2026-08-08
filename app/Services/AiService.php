<?php

namespace App\Services;

use App\AI\Providers\ProviderInterface;
use App\Jobs\EditSchemaJob;
use App\Jobs\GenerateFormJob;
use App\Models\AiJob;
use App\Models\Form;
use App\Support\AiPromptBuilder;
use App\Support\SchemaDiff;
use App\Support\SchemaValidator;
use RuntimeException;

class AiService
{
    public function __construct(protected FormService $forms) {}

    public function dispatchGeneration(Form $form, string $description): AiJob
    {
        $job = AiJob::create([
            'form_id' => $form->id,
            'kind' => 'generate',
            'prompt' => AiPromptBuilder::generate($description, $form->title),
            'model' => config('services.ai.model'),
            'status' => 'pending',
        ]);

        GenerateFormJob::dispatch($job);

        return $job;
    }

    public function dispatchEdit(Form $form, string $instruction): AiJob
    {
        $schema = $this->forms->ensureSchemaShape($form->schema ?? [], $form->title);

        $job = AiJob::create([
            'form_id' => $form->id,
            'kind' => 'edit',
            'prompt' => AiPromptBuilder::edit($schema, $instruction, $form->title),
            'model' => config('services.ai.model'),
            'status' => 'pending',
        ]);

        EditSchemaJob::dispatch($job);

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

    public function processEdit(AiJob $job): void
    {
        $form = $job->form;

        if (! $form) {
            throw new RuntimeException('AI job has no associated form.');
        }

        $model = $job->model ?? config('services.ai.model');

        $raw = $this->provider()->complete($job->prompt, $model);

        $after = SchemaValidator::parseAndRepair($raw);
        $after = SchemaValidator::validate($after, $form->title);

        $before = $this->forms->ensureSchemaShape($form->schema ?? [], $form->title);

        $diff = SchemaDiff::between($before, $after);
        $diff['schema'] = $after;

        $job->update([
            'status' => 'completed',
            'response' => $raw,
            'model' => $model,
            'diff' => $diff,
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
