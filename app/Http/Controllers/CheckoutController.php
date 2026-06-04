<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\StripeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use AuthorizesRequests;

    public function success(Request $request, StripeService $stripe, CartService $cartService, OrderService $orderService,)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('checkout.cancel');
        }

        $session = $stripe->retrieveSession($sessionId);
        $order = Order::where('stripe_session_id', $sessionId)->first();

        if (! $order) {
            logger()->error('Order not found for session: '.$sessionId);
            return redirect()->route('checkout.cancel');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('checkout.cancel');
        }

        if ($order->status !== 'paid') {
            $orderService->completeOrder($order, $session->payment_intent);
            $cartService->clear();
        }

        return view('checkout.success', compact('order'));
    }

    public function pay(Order $order, StripeService $stripe)
    {
        $this->authorize('view', $order);

        if ($order->status !== 'pending') {
            abort(403, "Order can't be paid again.");
        }

        foreach ($order->items as $item) {
            if ($item->variant) {
                if ($item->variant->stock < $item->quantity) {
                    $order->update([
                        'status' => 'cancelled',
                    ]);

                    return back()->with('error', sprintf(
                        'Unfortunately, there is insufficient stock for "%s (%s)". The order has been cancelled.', 
                        $item->product->name, 
                        $item->variant->name
                    ));
                }
            }
        }

        $lineItems = $order->items->map(function ($item) {
            $productName = $item->product->name ?? 'Product';
            if ($item->variant) {
                $productName .= ' (' . $item->variant->name . ')';
            }

            return [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $productName,
                    ],
                    'unit_amount' => $item->price,
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $session = $stripe->createCheckoutSession(
            $lineItems,
            route('checkout.success'),
            route('checkout.cancel')
        );

        $order->update([
            'stripe_session_id' => $session->id,
        ]);

        return redirect()->away($session->url);
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}