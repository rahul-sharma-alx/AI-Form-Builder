<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Services\FormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_returns_draft_with_schema(): void
    {
        $form = Form::factory()->create(['status' => 'draft', 'version' => 1]);

        $this->assertTrue($form->isDraft());
        $this->assertEquals(1, $form->version);
        $this->assertIsArray($form->schema);
        $this->assertEquals(1, count($form->schema['steps']));
    }

    public function test_autosave_without_schema_change_does_not_bump_version(): void
    {
        $form = Form::factory()->create(['version' => 3]);
        $service = app(FormService::class);

        $service->autosave($form, [
            'title' => 'Renamed only',
            'description' => 'A',
            'schema' => $form->schema,
            'settings' => [],
            'metadata' => [],
        ]);

        $this->assertEquals(3, $form->refresh()->version);
    }

    public function test_schema_change_bumps_version(): void
    {
        $form = Form::factory()->create(['version' => 1]);
        $service = app(FormService::class);

        $newSchema = $form->schema;
        $newSchema['steps'][0]['sections'][0]['fields'][] = ['id' => 'f1'];

        $service->autosave($form, [
            'title' => $form->title,
            'schema' => $newSchema,
            'settings' => [],
            'metadata' => [],
        ]);

        $this->assertEquals(2, $form->refresh()->version);
    }

    public function test_publish_sets_status_and_published_at(): void
    {
        $form = Form::factory()->create(['status' => 'draft']);
        $service = app(FormService::class);

        $service->publish($form);

        $this->assertTrue($form->refresh()->isPublished());
        $this->assertNotNull($form->published_at);
    }

    public function test_unpublish_clears_published_at(): void
    {
        $form = Form::factory()->published()->create();
        $service = app(FormService::class);

        $service->unpublish($form);

        $this->assertTrue($form->refresh()->isDraft());
        $this->assertNull($form->published_at);
    }

    public function test_settings_and_metadata_round_trip(): void
    {
        $form = Form::factory()->create();
        $service = app(FormService::class);

        $service->autosave($form, [
            'title' => $form->title,
            'schema' => $form->schema,
            'settings' => ['theme' => 'dark'],
            'metadata' => ['owner' => 'abc'],
        ]);

        $form->refresh();
        $this->assertEquals(['theme' => 'dark'], $form->settings);
        $this->assertEquals(['owner' => 'abc'], $form->metadata);
    }

    public function test_delete_is_soft(): void
    {
        $form = Form::factory()->create();

        $form->delete();

        $this->assertSoftDeleted('forms', ['id' => $form->id]);
        $this->assertNull(Form::find($form->id));
        $this->assertNotNull(Form::withTrashed()->find($form->id));
    }

    public function test_ensureSchemaShape_wraps_legacy_sections(): void
    {
        $service = app(FormService::class);

        $normalized = $service->ensureSchemaShape([
            'title' => 'Legacy',
            'sections' => [['id' => 's1', 'title' => 'S', 'fields' => []]],
        ]);

        $this->assertArrayHasKey('steps', $normalized);
        $this->assertCount(1, $normalized['steps']);
        $this->assertCount(1, $normalized['steps'][0]['sections']);
    }
}