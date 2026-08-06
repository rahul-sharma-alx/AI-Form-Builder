<?php

namespace Tests\Feature;

use App\Livewire\Forms\Builder;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_field_selects_it(): void
    {
        $form = Form::factory()->create();

        $component = Livewire::test(Builder::class, ['form' => $form]);

        $component->call('addField', 'text');

        $component->assertSet('selectedFieldId', $component->get('selectedField')['id']);

        $fieldId = $component->get('selectedFieldId');
        $this->assertNotNull($fieldId);

        $schema = $component->get('schema');
        $this->assertSame(
            $fieldId,
            $schema['steps'][0]['sections'][0]['fields'][0]['id']
        );
    }
}
