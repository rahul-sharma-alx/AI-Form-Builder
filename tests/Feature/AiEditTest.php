<?php

namespace Tests\Feature;

use App\AI\Providers\ProviderInterface;
use App\Jobs\EditSchemaJob;
use App\Livewire\Ai\Edit;
use App\Models\Form;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class AiEditFakeProvider implements ProviderInterface
{
    public static string $response = '';

    public function complete(string $prompt, string $model): string
    {
        return static::$response;
    }
}

class AiEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai.provider', AiEditFakeProvider::class);
        config()->set('services.ai.model', 'fake-model');
        config()->set('queue.default', 'database');
    }

    protected function baseSchema(): array
    {
        return [
            'title' => 'Survey',
            'steps' => [
                [
                    'id' => 's1',
                    'title' => 'Step 1',
                    'sections' => [
                        [
                            'id' => 'sec1',
                            'title' => 'Section 1',
                            'fields' => [
                                ['id' => 'f1', 'type' => 'text', 'key' => 'full_name', 'label' => 'Full Name', 'required' => true],
                                ['id' => 'f2', 'type' => 'email', 'key' => 'email', 'label' => 'Email'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function editedSchemaJson(): string
    {
        $schema = $this->baseSchema();
        $schema['steps'][0]['sections'][0]['fields'] = [
            ['id' => 'f1', 'type' => 'text', 'key' => 'full_name', 'label' => 'Nombre Completo', 'required' => true],
            ['id' => 'f3', 'type' => 'rating', 'key' => 'satisfaction', 'label' => 'Satisfaction'],
        ];

        return json_encode($schema);
    }

    public function test_start_creates_pending_edit_job(): void
    {
        $form = Form::factory()->create(['schema' => $this->baseSchema()]);

        Livewire::test(Edit::class, ['form' => $form])
            ->set('instruction', 'Translate labels to Spanish')
            ->call('start')
            ->assertOk()
            ->assertSee('Applying your edits');

        $this->assertDatabaseHas('ai_jobs', [
            'form_id' => $form->id,
            'kind' => 'edit',
            'status' => 'pending',
        ]);
    }

    public function test_job_computes_diff_without_autosaving_schema(): void
    {
        AiEditFakeProvider::$response = $this->editedSchemaJson();
        $form = Form::factory()->create(['title' => 'Survey', 'schema' => $this->baseSchema()]);

        $job = app(AiService::class)->dispatchEdit($form, 'Translate labels, drop email, add rating');
        (new EditSchemaJob($job))->handle(app(AiService::class));

        $job->refresh();
        $form->refresh();

        $this->assertSame('completed', $job->status);
        $this->assertNotNull($job->diff);

        $changes = collect($job->diff['changes'])->mapWithKeys(
            fn ($change) => [$change['change'] => $change['entity']]
        )->all();

        $this->assertSame('field', $changes['added']);
        $this->assertSame('field', $changes['removed']);
        $this->assertSame('field', $changes['modified']);

        $this->assertSame('Full Name', $form->schema['steps'][0]['sections'][0]['fields'][0]['label']);
        $this->assertCount(2, $form->schema['steps'][0]['sections'][0]['fields']);
    }

    public function test_apply_saves_edited_schema_preserving_ids(): void
    {
        AiEditFakeProvider::$response = $this->editedSchemaJson();
        $form = Form::factory()->create(['title' => 'Survey', 'schema' => $this->baseSchema()]);

        $job = app(AiService::class)->dispatchEdit($form, 'Translate labels, drop email, add rating');
        (new EditSchemaJob($job))->handle(app(AiService::class));

        Livewire::test(Edit::class, ['form' => $form])
            ->set('jobId', $job->id)
            ->call('apply')
            ->assertHasNoErrors();

        $form->refresh();
        $fields = $form->schema['steps'][0]['sections'][0]['fields'];

        $this->assertSame('f1', $fields[0]['id']);
        $this->assertSame('Nombre Completo', $fields[0]['label']);
        $this->assertCount(2, $fields);
    }

    public function test_job_marks_failed_when_edited_schema_invalid(): void
    {
        AiEditFakeProvider::$response = '{"not":"a schema"}';
        $form = Form::factory()->create(['schema' => $this->baseSchema()]);

        $job = app(AiService::class)->dispatchEdit($form, 'Break it');

        try {
            (new EditSchemaJob($job))->handle(app(AiService::class));
        } catch (RuntimeException $e) {
            (new EditSchemaJob($job))->failed($e);
        }

        $job->refresh();

        $this->assertSame('failed', $job->status);
        $this->assertNotNull($job->error_message);
        $this->assertDatabaseHas('ai_jobs', ['id' => $job->id, 'status' => 'failed']);
    }
}
