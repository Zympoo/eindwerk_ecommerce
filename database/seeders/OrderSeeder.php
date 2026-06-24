<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::whereHas('role', function($query) {
            $query->where('name', 'customer');
        })->get();

        $products = Product::with('variants')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($customers->random(5) as $customer) {
            $order = Order::factory()->create([
                'user_id' => $customer->id,
            ]);

            $randomProducts = $products->random(rand(1, 3));

            foreach ($randomProducts as $product) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $product->variants->first()?->id,
                    'price' => $product->price,
                ]);
            }
        }
    }
}