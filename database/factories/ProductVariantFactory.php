<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->randomElement(['Size M', 'Size L', 'Red', 'Blue']),
            'additional_price' => $this->faker->numberBetween(0, 2000),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}