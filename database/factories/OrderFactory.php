<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'total_price' => $this->faker->numberBetween(1000, 50000),
            'status' => $this->faker->randomElement(['pending', 'completed']),
            'shipping_address' => [
                'street' => $this->faker->streetName(),
                'house_number' => $this->faker->buildingNumber(),
                'city' => $this->faker->city(),
                'postcode' => $this->faker->postcode(),
                'country' => $this->faker->country(),
            ],
            'stripe_payment_intent_id' => 'pi_' . Str::random(24),
            'stripe_session_id' => 'cs_' . Str::random(24),
        ];
    }
}