<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_redirects_to_forms_index(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('forms.index'));
    }

    public function test_forms_index_renders(): void
    {
        $this->get(route('forms.index'))->assertOk();
    }
}
