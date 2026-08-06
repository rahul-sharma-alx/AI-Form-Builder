<?php

namespace Database\Factories;

use App\Models\Form;
use App\Support\SchemaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'schema' => SchemaFactory::create($this->faker->words(3, true)),
            'settings' => [],
            'metadata' => [],
            'status' => 'draft',
            'version' => 1,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
