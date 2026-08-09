<?php

namespace Tests\Feature;

use App\Livewire\Forms\Create;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TemplateLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_with_template_uses_template_schema(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'My Contact Form')
            ->set('templateId', 'contact')
            ->call('save')
            ->assertRedirect();

        $form = Form::latest('id')->first();
        $this->assertSame('My Contact Form', $form->title);

        $fields = $form->schema['steps'][0]['sections'][0]['fields'];
        $this->assertCount(3, $fields);
        $this->assertSame('email', $fields[1]['type']);
    }

    public function test_create_blank_form_has_empty_section(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'Blank')
            ->call('save')
            ->assertRedirect();

        $form = Form::latest('id')->first();
        $this->assertCount(0, $form->schema['steps'][0]['sections'][0]['fields']);
    }

    public function test_unknown_template_falls_back_to_blank(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'Whatever')
            ->set('templateId', 'does-not-exist')
            ->call('save')
            ->assertRedirect();

        $form = Form::latest('id')->first();
        $this->assertCount(0, $form->schema['steps'][0]['sections'][0]['fields']);
    }

    public function test_clicking_template_creates_form_and_opens_builder(): void
    {
        $response = Livewire::test(Create::class)
            ->call('startFromTemplate', 'contact');

        $response->assertRedirect();

        $form = Form::latest('id')->first();
        $this->assertNotNull($form);
        $this->assertSame('Contact Form', $form->title);
        $this->assertSame($response->effects['redirect'], route('forms.builder', $form));

        $fields = $form->schema['steps'][0]['sections'][0]['fields'];
        $this->assertCount(3, $fields);
        $this->assertSame('email', $fields[1]['type']);
    }
}
