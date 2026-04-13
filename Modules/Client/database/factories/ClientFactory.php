<?php

namespace Modules\Client\Database\Factories;

use Modules\Client\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $documentType = fake()->randomElement(['CPF', 'CNPJ']);
        $documentNumber = $documentType === 'CPF' 
            ? fake()->numerify('###########')
            : fake()->numerify('##############');

        return [
            'user_id' => null,
            'name' => fake()->company(),
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'phone1' => fake()->phoneNumber(),
            'contact1' => fake()->name(),
            'phone2' => fake()->optional(0.6)->phoneNumber(),
            'contact2' => fake()->optional(0.6)->name(),
            'phone' => fake()->phoneNumber(),
            'state_registration' => fake()->optional(0.7)->numerify('#########'),
            'municipal_registration' => fake()->optional(0.5)->numerify('#########'),
            'contributor_type' => fake()->randomElement([1, 2, 9]),
            'is_active' => true,
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'CPF',
            'document_number' => fake()->numerify('###########'),
            'state_registration' => null,
            'municipal_registration' => null,
            'contributor_type' => fake()->randomElement([2, 9]),
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'CNPJ',
            'document_number' => fake()->numerify('##############'),
            'state_registration' => fake()->numerify('#########'),
            'municipal_registration' => fake()->numerify('#########'),
            'contributor_type' => fake()->randomElement([1, 2]),
        ]);
    }

    public function icmsContributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'contributor_type' => 1,
            'state_registration' => fake()->numerify('#########'),
        ]);
    }

    public function icmsExempt(): static
    {
        return $this->state(fn (array $attributes) => [
            'contributor_type' => 2,
            'state_registration' => 'ISENTO',
        ]);
    }

    public function nonContributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'contributor_type' => 9,
            'state_registration' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
