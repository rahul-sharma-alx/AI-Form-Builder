<?php

namespace Tests\Feature;

use App\Livewire\Builder\PropertyPanel;
use App\Support\FieldFactory;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyPanelTest extends TestCase
{
    public function test_invalid_validation_is_blocked_and_error_shown(): void
    {
        $field = array_merge(FieldFactory::make('text'), ['validation' => 'min:5']);

        Livewire::test(PropertyPanel::class)
            ->dispatch('field-selected', field: $field)
            ->set('field.validation', 'not_a_real_rule')
            ->assertSet('validationError', fn ($v) => $v !== '')
            ->assertDispatched('field-update', fn ($name, $params) => ($params['field']['validation'] ?? null) === 'min:5');
    }

    public function test_valid_validation_is_propagated(): void
    {
        $field = FieldFactory::make('text');

        Livewire::test(PropertyPanel::class)
            ->dispatch('field-selected', field: $field)
            ->set('field.validation', 'max:50')
            ->assertSet('validationError', '')
            ->assertDispatched('field-update', fn ($name, $params) => ($params['field']['validation'] ?? null) === 'max:50');
    }
}
