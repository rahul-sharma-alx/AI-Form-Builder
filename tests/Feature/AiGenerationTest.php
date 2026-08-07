<?php

namespace Tests\Feature;

use App\AI\Providers\ProviderInterface;
use App\Jobs\GenerateFormJob;
use App\Livewire\Ai\Generate;
use App\Models\AiJob;
use App\Models\Form;
use App\Services\AiService;
use App\Support\AiPromptBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class AiFakeProvider implements ProviderInterface
{
    public static string $response = '';

    public function complete(string $prompt, string $model): string
    {
        return static::$response;
    }
}

class AiGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai.provider', AiFakeProvider::class);
        config()->set('services.ai.model', 'fake-model');
    }

    protected function validSchemaJson(): string
    {
        return json_encode([
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
                                ['type' => 'text', 'key' => 'full_name', 'label' => 'Full Name', 'required' => true],
                                ['type' => 'email', 'key' => 'email', 'label' => 'Email'],
                                ['type' => 'dropdown', 'key' => 'topic', 'label' => 'Topic', 'options' => [
                                    ['label' => 'A', 'value' => 'a'],
                                    ['label' => 'B', 'value' => 'b'],
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_start_creates_pending_job_and_shows_progress(): void
    {
        config()->set('queue.default', 'database');
        $form = Form::factory()->create();

        Livewire::test(Generate::class, ['form' => $form])
            ->set('description', 'A survey')
            ->call('start')
            ->assertOk()
            ->assertSee('Generating');

        $this->assertDatabaseHas('ai_jobs', [
            'form_id' => $form->id,
            'status' => 'pending',
        ]);
    }

    public function test_job_completes_and_saves_schema_to_form(): void
    {
        AiFakeProvider::$response = $this->validSchemaJson();
        $form = Form::factory()->create(['title' => 'Survey']);

        $job = app(AiService::class)->dispatchGeneration($form, 'A survey');
        (new GenerateFormJob($job))->handle(app(AiService::class));

        $job->refresh();

        $this->assertSame('completed', $job->status);
        $this->assertSame('fake-model', $job->model);

        $form->refresh();
        $schema = $form->schema;

        $this->assertSame('Survey', $schema['title']);
        $this->assertCount(1, $schema['steps']);
        $this->assertSame('full_name', $schema['steps'][0]['sections'][0]['fields'][0]['key']);
        $this->assertSame('text', $schema['steps'][0]['sections'][0]['fields'][0]['type']);
    }

    public function test_job_repairs_fenced_and_trailing_comma_json(): void
    {
        AiFakeProvider::$response = "```json\n".preg_replace('/"\]/', '",]', $this->validSchemaJson(), 1)."\n```";
        $form = Form::factory()->create();

        $job = app(AiService::class)->dispatchGeneration($form, 'A survey');
        (new GenerateFormJob($job))->handle(app(AiService::class));

        $this->assertSame('completed', $job->fresh()->status);
    }

    public function test_job_marks_failed_when_schema_invalid(): void
    {
        AiFakeProvider::$response = '{"foo":"bar"}';
        $form = Form::factory()->create();

        $job = AiJob::create([
            'form_id' => $form->id,
            'prompt' => AiPromptBuilder::generate('A survey', $form->title),
            'model' => 'fake-model',
            'status' => 'pending',
        ]);

        try {
            (new GenerateFormJob($job))->handle(app(AiService::class));
        } catch (RuntimeException $e) {
            (new GenerateFormJob($job))->failed($e);
        }

        $job->refresh();

        $this->assertSame('failed', $job->status);
        $this->assertNotNull($job->error_message);
    }
}
