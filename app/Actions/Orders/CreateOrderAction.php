<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\StripeService;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function __construct(
        protected StripeService $stripe
    ) {}

    public function handle(array $cartItems, array $address, ?int $userId): string
    {
        $totalPrice = $this->calculateTotal($cartItems);

        $order = Order::create([
            'user_id' => $userId,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'total_price' => $totalPrice,
            'status' => 'pending',
            'shipping_address' => $address, // Wordt gecast naar JSON via het Model
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'], // De reeds berekende prijs incl. variant meerprijs
            ]);
        }

        // Maak Stripe Checkout items
        $lineItems = collect($cartItems)->map(fn($item) => [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['name'],
                ],
                'unit_amount' => $item['price'], // Prijzen staan al in centen in je DB
            ],
            'quantity' => $item['quantity'],
        ])->values()->toArray();

        $session = $this->stripe->createCheckoutSession(
            $lineItems,
            route('checkout.success'),
            route('checkout.cancel')
        );

        $order->update([
            'stripe_session_id' => $session->id,
        ]);

        return $session->url;
    }

    private function calculateTotal(array $cartItems): int
    {
        return collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);
    }
}