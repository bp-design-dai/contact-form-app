<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'first_name' => $this->faker->lastName(),
            'last_name' => $this->faker->firstName(),
            'gender' => $this->faker->numberBetween(1, 3),
            'email' => $this->faker->unique()->safeEmail(),
            'tel' => $this->faker->numerify('090########'),
            'address' => $this->faker->address(),
            'building' => $this->faker->optional()->secondaryAddress(),
            'detail' => $this->faker->realText(80),
        ];
    }
}
