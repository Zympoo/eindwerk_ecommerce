<?php

namespace App\Actions\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AddItemToCartAction
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle(Product $product, int $quantity, ?int $variantId = null): void
    {
        $stock = $variantId 
            ? ProductVariant::where('product_id', $product->id)->find($variantId)?->stock ?? 0
            : 999; // Fallback als er geen variant is

        $quantity = max(1, min($quantity, $stock));

        if (Auth::check()) {
            $cart = $this->cartService->getCart();

            $item = $cart->items()->firstOrNew([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
            ]);

            $item->quantity = min(($item->quantity ?? 0) + $quantity, $stock);
            $item->save();
            return;
        }

        $cart = Session::get($this->cartService->getSession(), []);
        $key = $variantId ? "{$product->id}-{$variantId}" : (string)$product->id;

        if (!isset($cart[$key])) {
            $cart[$key] = [
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => 0,
            ];
        }

        $cart[$key]['quantity'] = min($cart[$key]['quantity'] + $quantity, $stock);
        Session::put($this->cartService->getSession(), $cart);
    }
}