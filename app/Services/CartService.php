<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart';

    public function getCart()
    {
        return Auth::check()
            ? Cart::firstOrCreate(['user_id' => Auth::id()])
            : null;
    }

    public function getItems()
    {
        if (Auth::check()) {
            $cart = $this->getCart();
            if (!$cart) return collect();

            return $cart->items()
                ->with(['product', 'variant'])
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'id' => $item->id, // Belangrijk voor database updates/deletes
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'product' => (object) [
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                        ],
                        'variant' => $item->variant ? (object) [
                            'id' => $item->variant->id,
                            'name' => $item->variant->name,
                            'additional_price' => $item->variant->additional_price,
                            'stock' => $item->variant->stock,
                        ] : null,
                    ];
                });
        }

        // Sessie logica voor gasten
        return collect(Session::get($this->sessionKey, []))
            ->map(function ($item, $key) {
                $product = Product::find($item['product_id']);
                $variant = isset($item['product_variant_id']) ? ProductVariant::find($item['product_variant_id']) : null;

                if (!$product) return null;

                return (object) [
                    'id' => $key, // De unieke array-key dient als ID in de sessie
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'product' => (object) [
                        'name' => $product->name,
                        'price' => $product->price,
                    ],
                    'variant' => $variant ? (object) [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'additional_price' => $variant->additional_price,
                        'stock' => $variant->stock,
                    ] : null,
                ];
            })->filter()->values();
    }

    public function clear(): void
    {
        if (Auth::check()) {
            $cart = $this->getCart();
            if ($cart) {
                $cart->items()->delete();
            }
        } else {
            Session::forget($this->sessionKey);
        }
    }

    public function getSession(): string
    {
        return $this->sessionKey;
    }
}