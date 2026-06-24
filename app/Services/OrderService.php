<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function completeOrder(Order $order, string $paymentIntentId): void
    {
        if ($order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order, $paymentIntentId) {
            $order->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId,
            ]);

            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                }
            }
        });
    }
}