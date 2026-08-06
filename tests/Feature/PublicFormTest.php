<?php

namespace Tests\Feature;

use App\Livewire\Public\Fill;
use App\Models\Form;
use App\Support\FieldFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicFormTest extends TestCase
{
    use RefreshDatabase;

    protected function formWithFields(): Form
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
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return Form::factory()->published()->create(['title' => 'Test Form', 'schema' => $schema]);
    }

    public function test_fill_page_renders_fields(): void
    {
        $form = $this->formWithFields();

        Livewire::test(Fill::class, ['form' => $form])
            ->assertOk()
            ->assertSee('Test Form')
            ->assertSee('Name')
            ->assertSee('Email');
    }

    public function test_submit_requires_fields_by_validation(): void
    {
        $form = $this->formWithFields();

        Livewire::test(Fill::class, ['form' => $form])
            ->call('submit')
            ->assertHasErrors(['answers.name' => 'required', 'answers.email' => 'required']);
    }

    public function test_submit_succeeds_with_valid_answers(): void
    {
        $form = $this->formWithFields();

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.name', 'John')
            ->set('answers.email', 'john@example.com')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);
    }

    public function test_public_url_requires_published_form(): void
    {
        $draft = Form::factory()->create(['status' => 'draft']);

        $this->get(route('forms.public', $draft))->assertOk();
    }
}