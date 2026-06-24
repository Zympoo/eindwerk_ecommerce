<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

class UpdateCartItemAction
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle($itemId, int $quantity): void
    {
        if (Auth::check()) {
            $item = CartItem::with('variant')->find($itemId);
            if (!$item) return;

            $stock = $item->variant?->stock ?? 0;

            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->update(['quantity' => max(1, min($quantity, $stock))]);
            }

            Livewire::current()->dispatch('cart_updated');
            return;
        }

        $cart = Session::get($this->cartService->getSession(), []);
        if (isset($cart[$itemId])) {
            if ($quantity <= 0) {
                unset($cart[$itemId]);
            } else {
                $stock = ProductVariant::find($cart[$itemId]['product_variant_id'])?->stock ?? 0;

                $cart[$itemId]['quantity'] = max(1, min($quantity, $stock));
            }
            Session::put($this->cartService->getSession(), $cart);

            Livewire::current()->dispatch('cart_updated');
        }
    }
}