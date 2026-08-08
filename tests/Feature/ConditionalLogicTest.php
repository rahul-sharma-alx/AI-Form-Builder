<?php

namespace Tests\Feature;

use App\Livewire\Public\Fill;
use App\Models\Form;
use App\Support\SchemaConditions;
use App\Support\SchemaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConditionalLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function conditionalForm(): Form
    {
        $schema = SchemaFactory::create('Conditional');

        $schema['steps'][0]['sections'][0]['fields'] = [
            [
                'id' => 'q1',
                'type' => 'dropdown',
                'key' => 'trigger',
                'label' => 'Trigger',
                'required' => true,
                'options' => [
                    ['label' => 'Yes', 'value' => 'yes'],
                    ['label' => 'No', 'value' => 'no'],
                ],
                'validation' => [],
            ],
            [
                'id' => 'q2',
                'type' => 'text',
                'key' => 'conditional',
                'label' => 'Conditional',
                'required' => true,
                'visibility' => ['field' => 'trigger', 'op' => 'equals', 'value' => 'yes'],
                'validation' => [],
            ],
        ];

        return Form::factory()->create(['schema' => $schema]);
    }

    public function test_visibility_rules(): void
    {
        $this->assertTrue(SchemaConditions::visible(null, []));
        $this->assertTrue(SchemaConditions::visible(['field' => 'a', 'op' => 'equals', 'value' => 'x'], ['a' => 'x']));
        $this->assertFalse(SchemaConditions::visible(['field' => 'a', 'op' => 'equals', 'value' => 'x'], ['a' => 'y']));
        $this->assertTrue(SchemaConditions::visible(['field' => 'a', 'op' => 'not_equals', 'value' => 'x'], ['a' => 'y']));
        $this->assertTrue(SchemaConditions::visible(['field' => 'a', 'op' => 'empty', 'value' => null], ['a' => '']));
        $this->assertTrue(SchemaConditions::visible(['field' => 'a', 'op' => 'not_empty', 'value' => null], ['a' => 'z']));
        $this->assertFalse(SchemaConditions::visible(['field' => 'a', 'op' => 'not_empty', 'value' => null], ['a' => '']));
    }

    public function test_hidden_field_is_not_validated(): void
    {
        Livewire::test(Fill::class, ['form' => $this->conditionalForm()])
            ->set('answers.trigger', 'no')
            ->set('answers.conditional', null)
            ->call('submit')
            ->assertHasNoErrors();
    }

    public function test_visible_required_field_is_validated(): void
    {
        Livewire::test(Fill::class, ['form' => $this->conditionalForm()])
            ->set('answers.trigger', 'yes')
            ->set('answers.conditional', null)
            ->call('submit')
            ->assertHasErrors(['answers.conditional']);
    }

    public function test_hidden_answer_is_stripped_on_submit(): void
    {
        $form = $this->conditionalForm();

        Livewire::test(Fill::class, ['form' => $form])
            ->set('answers.trigger', 'no')
            ->set('answers.conditional', 'stale value')
            ->call('submit')
            ->assertHasNoErrors();

        $data = $form->submissions()->latest('id')->first()->data;
        $this->assertArrayNotHasKey('conditional', $data);
        $this->assertSame('no', $data['trigger']);
    }
}
