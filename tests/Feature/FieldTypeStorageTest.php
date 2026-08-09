<?php

namespace Tests\Feature;

use App\Livewire\Public\Fill;
use App\Models\Form;
use App\Models\Submission;
use App\Support\FieldFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FieldTypeStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function formWith(array $fields): Form
    {
        $schema = [
            'title' => 'T',
            'steps' => [[
                'id' => 's1',
                'title' => 'Step 1',
                'sections' => [[
                    'id' => 'sec1',
                    'title' => 'Section 1',
                    'fields' => $fields,
                ]],
            ]],
        ];

        return Form::factory()->published()->create(['title' => 'T', 'schema' => $schema]);
    }

    public function test_rating_accepts_any_star_between_one_and_max(): void
    {
        // The property panel stores "Max Stars" in `max`.
        $form = $this->formWith([
            array_merge(FieldFactory::make('rating'), ['key' => 'satisfaction', 'required' => true, 'max' => 5]),
        ]);

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.satisfaction', '4')
            ->call('submit')
            ->assertHasNoErrors();

        $submission = Submission::latest('id')->first();
        $this->assertSame('4', $submission->data['satisfaction'] ?? null);
    }

    public function test_rating_rejects_value_above_max_stars(): void
    {
        $form = $this->formWith([
            array_merge(FieldFactory::make('rating'), ['key' => 'satisfaction', 'required' => true, 'max' => 3]),
        ]);

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.satisfaction', '5')
            ->call('submit')
            ->assertHasErrors(['answers.satisfaction' => 'max']);
    }

    public function test_dropdown_and_checkbox_values_are_stored(): void
    {
        $form = $this->formWith([
            array_merge(FieldFactory::make('dropdown'), ['key' => 'ticket', 'required' => true, 'options' => [
                ['label' => 'General', 'value' => 'general'],
                ['label' => 'VIP', 'value' => 'vip'],
            ]]),
            array_merge(FieldFactory::make('checkbox'), ['key' => 'contact', 'required' => false, 'options' => [
                ['label' => 'Yes', 'value' => 'yes'],
            ]]),
        ]);

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.ticket', 'vip')
            ->set('answers.contact', ['yes'])
            ->call('submit')
            ->assertHasNoErrors();

        $submission = Submission::latest('id')->first();
        $this->assertSame('vip', $submission->data['ticket'] ?? null);
        $this->assertSame(['yes'], $submission->data['contact'] ?? null);
    }

    public function test_fully_unchecked_checkbox_is_not_stored_and_passes_not_required(): void
    {
        $form = $this->formWith([
            array_merge(FieldFactory::make('checkbox'), ['key' => 'contact', 'required' => false, 'options' => [
                ['label' => 'Yes', 'value' => 'yes'],
            ]]),
        ]);

        // Livewire leaves false elements behind for unchecked boxes.
        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.contact', [0 => false])
            ->call('submit')
            ->assertHasNoErrors();

        $submission = Submission::latest('id')->first();
        $this->assertArrayNotHasKey('contact', $submission->data);
    }

    public function test_required_checkbox_blocks_when_all_unchecked(): void
    {
        $form = $this->formWith([
            array_merge(FieldFactory::make('checkbox'), ['key' => 'agree', 'required' => true, 'options' => [
                ['label' => 'Agree', 'value' => 'yes'],
            ]]),
        ]);

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.agree', [0 => false])
            ->call('submit')
            ->assertHasErrors(['answers.agree' => 'required']);
    }

    public function test_html_block_renders_content_on_public_form(): void
    {
        $form = $this->formWith([
            array_merge(FieldFactory::make('html'), ['label' => '', 'content' => '<p>Hello <strong>world</strong></p>']),
        ]);

        Livewire::test(Fill::class, ['form' => $form])
            ->assertOk()
            ->assertSee('Hello', false)
            ->assertSee('world', false);
    }
}
