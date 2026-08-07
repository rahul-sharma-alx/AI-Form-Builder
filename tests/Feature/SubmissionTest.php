<?php

namespace Tests\Feature;

use App\Livewire\Public\Fill;
use App\Livewire\Submissions\Index as SubmissionsIndex;
use App\Models\Form;
use App\Models\Submission;
use App\Support\FieldFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function formWithFields(array $settings = []): Form
    {
        $schema = [
            'title' => 'Test Form',
            'steps' => [
                [
                    'id' => 's1',
                    'title' => 'Step 1',
                    'sections' => [
                        [
                            'id' => 'sec1',
                            'title' => 'Section 1',
                            'fields' => [
                                array_merge(FieldFactory::make('text'), ['label' => 'Name', 'key' => 'name', 'required' => true]),
                                array_merge(FieldFactory::make('email'), ['label' => 'Email', 'key' => 'email', 'required' => true]),
                                array_merge(FieldFactory::make('checkbox'), ['label' => 'Interests', 'key' => 'interests', 'options' => [
                                    ['label' => 'A', 'value' => 'a'],
                                    ['label' => 'B', 'value' => 'b'],
                                ]]),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return Form::factory()->published()->create(['title' => 'Test Form', 'schema' => $schema, 'settings' => $settings]);
    }

    public function test_submit_stores_a_submission(): void
    {
        $form = $this->formWithFields();

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.name', 'John')
            ->set('answers.email', 'john@example.com')
            ->set('answers.interests', ['a'])
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('submissions', [
            'form_id' => $form->id,
            'ip_address' => '127.0.0.1',
        ]);

        $submission = Submission::where('form_id', $form->id)->firstOrFail();
        $this->assertSame('John', $submission->data['name']);
        $this->assertSame('john@example.com', $submission->data['email']);
        $this->assertSame(['a'], $submission->data['interests']);
    }

    public function test_submissions_index_lists_and_searches(): void
    {
        $form = $this->formWithFields();

        Submission::create(['form_id' => $form->id, 'data' => ['name' => 'Alice', 'email' => 'a@b.com'], 'ip_address' => '127.0.0.1']);
        Submission::create(['form_id' => $form->id, 'data' => ['name' => 'Bob', 'email' => 'b@b.com'], 'ip_address' => '127.0.0.1']);

        Livewire::test(SubmissionsIndex::class, ['form' => $form])
            ->assertSee('Alice')
            ->assertSee('Bob')
            ->assertSee('Name');

        Livewire::test(SubmissionsIndex::class, ['form' => $form])
            ->set('search', 'Alice')
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    public function test_export_downloads_csv(): void
    {
        $form = $this->formWithFields();

        Submission::create(['form_id' => $form->id, 'data' => ['name' => 'Alice', 'email' => 'a@b.com', 'interests' => ['a', 'b']], 'ip_address' => '127.0.0.1']);

        Livewire::test(SubmissionsIndex::class, ['form' => $form])
            ->call('export')
            ->assertFileDownloaded('submissions.csv');
    }

    public function test_rate_limit_blocks_excess_submissions(): void
    {
        $form = $this->formWithFields(['rate_limit' => 2]);

        foreach (['a', 'b'] as $i) {
            Livewire::test(Fill::class, ['form' => $form])
                ->set('answers.name', 'N'.$i)
                ->set('answers.email', $i.'@x.com')
                ->call('submit')
                ->assertHasNoErrors();
        }

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.name', 'N3')
            ->set('answers.email', '3@x.com')
            ->call('submit')
            ->assertHasErrors(['_form']);

        $this->assertSame(2, Submission::where('form_id', $form->id)->count());

        RateLimiter::clear('submissions:'.$form->id.':127.0.0.1');
    }
}
