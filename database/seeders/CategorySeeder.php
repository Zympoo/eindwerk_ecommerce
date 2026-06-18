<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()
            ->count(5)
            ->has(
                Product::factory()
                    ->count(10)
                    ->has(ProductVariant::factory()->count(5), 'variants'),
                'products'
            )
            ->create();
    }
}