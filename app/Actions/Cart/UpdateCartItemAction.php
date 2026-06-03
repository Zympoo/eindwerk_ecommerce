<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UpdateCartItemAction
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle($itemId, int $quantity): void
    {
        if (Auth::check()) {
            $item = CartItem::find($itemId);
            if (!$item) return;

            $stock = $item->product_variant_id 
                ? ProductVariant::find($item->product_variant_id)?->stock ?? 0 
                : 999;

            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->update(['quantity' => max(1, min($quantity, $stock))]);
            }
            return;
        }

        // Gasten / Sessie
        $cart = Session::get($this->cartService->getSession(), []);
        if (isset($cart[$itemId])) {
            if ($quantity <= 0) {
                unset($cart[$itemId]);
            } else {
                $stock = isset($cart[$itemId]['product_variant_id']) 
                    ? ProductVariant::find($cart[$itemId]['product_variant_id'])?->stock ?? 0 
                    : 999;

                $cart[$itemId]['quantity'] = max(1, min($quantity, $stock));
            }
            Session::put($this->cartService->getSession(), $cart);
        }
    }
}