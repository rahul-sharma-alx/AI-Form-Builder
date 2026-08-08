<?php

namespace Tests\Feature;

use App\Livewire\Forms\Builder;
use App\Livewire\Forms\Versions;
use App\Models\Form;
use App\Services\FormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BuilderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_undo_restores_previous_schema_and_redo_reapplies(): void
    {
        $form = Form::factory()->create();

        $component = Livewire::test(Builder::class, ['form' => $form])
            ->call('addField', 'text')
            ->call('addField', 'email');

        $this->assertCount(2, $component->get('schema')['steps'][0]['sections'][0]['fields']);

        $component->call('undo');
        $this->assertCount(1, $component->get('schema')['steps'][0]['sections'][0]['fields']);
        $this->assertSame(
            'text',
            $component->get('schema')['steps'][0]['sections'][0]['fields'][0]['type']
        );

        $component->call('undo');
        $this->assertCount(0, $component->get('schema')['steps'][0]['sections'][0]['fields']);

        $component->call('redo');
        $this->assertCount(1, $component->get('schema')['steps'][0]['sections'][0]['fields']);

        $component->call('redo');
        $this->assertCount(2, $component->get('schema')['steps'][0]['sections'][0]['fields']);
    }

    public function test_new_mutation_after_undo_clears_redo_stack(): void
    {
        $form = Form::factory()->create();

        $component = Livewire::test(Builder::class, ['form' => $form])
            ->call('addField', 'text')
            ->call('addField', 'email')
            ->call('undo');

        $this->assertCount(1, $component->get('schema')['steps'][0]['sections'][0]['fields']);

        $component->call('addField', 'date');

        $component->call('redo');
        $this->assertCount(2, $component->get('schema')['steps'][0]['sections'][0]['fields']);
    }

    public function test_save_records_version_snapshot(): void
    {
        $form = Form::factory()->create(['version' => 1]);
        $service = app(FormService::class);

        $schema = $form->schema;
        $schema['steps'][0]['sections'][0]['fields'][] = ['id' => 'f1'];

        $service->save($form, [
            'title' => $form->title,
            'schema' => $schema,
            'settings' => [],
            'metadata' => [],
        ]);

        $form->refresh();
        $this->assertSame(2, $form->version);
        $this->assertSame(1, $form->formVersions()->count());
        $this->assertSame(2, $form->formVersions()->first()->version);
    }

    public function test_schema_change_without_difference_does_not_record(): void
    {
        $form = Form::factory()->create(['version' => 4]);
        $service = app(FormService::class);

        $service->save($form, [
            'title' => $form->title,
            'schema' => $form->schema,
            'settings' => [],
            'metadata' => [],
        ]);

        $this->assertSame(4, $form->refresh()->version);
        $this->assertSame(0, $form->formVersions()->count());
    }

    public function test_rollback_restores_schema_and_records_new_version(): void
    {
        $form = Form::factory()->create(['version' => 1]);
        $service = app(FormService::class);

        $v1 = $form->schema;
        $v2 = $v1;
        $v2['steps'][0]['sections'][0]['fields'][] = ['id' => 'f1'];
        $service->save($form, ['title' => $form->title, 'schema' => $v2, 'settings' => [], 'metadata' => []]);

        $v3 = $v2;
        $v3['steps'][0]['sections'][0]['fields'][] = ['id' => 'f2'];
        $service->save($form, ['title' => $form->title, 'schema' => $v3, 'settings' => [], 'metadata' => []]);

        $first = $form->formVersions()->oldest('id')->first();

        $service->rollback($form, $first);

        $form->refresh();
        $this->assertSame(4, $form->version);
        $this->assertCount(1, $form->schema['steps'][0]['sections'][0]['fields']);
        $this->assertSame(3, $form->formVersions()->count());
    }

    public function test_versions_page_lists_and_rolls_back(): void
    {
        $form = Form::factory()->create(['version' => 1]);
        $service = app(FormService::class);

        $schema = $form->schema;
        $schema['steps'][0]['sections'][0]['fields'][] = ['id' => 'f1'];
        $service->save($form, ['title' => $form->title, 'schema' => $schema, 'settings' => [], 'metadata' => []]);

        $version = $form->formVersions()->first();

        Livewire::test(Versions::class, ['form' => $form->refresh()])
            ->call('rollback', $version->id)
            ->assertRedirect(route('forms.builder', $form));
    }
}
