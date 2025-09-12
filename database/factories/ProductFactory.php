<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3), // كيخترع سمية من 3 كلمات
            'description' => $this->faker->paragraph(4), // كيخترع وصف من 4 سطور
            'price' => $this->faker->randomFloat(2, 10, 1000), // كيخترع ثمن عشري بين 10 و 1000
            'image_url' => $this->faker->imageUrl(), // كيجيب رابط ديال صورة وهمية
        ];
    }
}