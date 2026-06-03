<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RemoveCartItemAction
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle($itemId): void
    {
        if (Auth::check()) {
            CartItem::where('id', $itemId)->delete();
            return;
        }

        $cart = Session::get($this->cartService->getSession(), []);
        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
        }
        Session::put($this->cartService->getSession(), $cart);
    }
}