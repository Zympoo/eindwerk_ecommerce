<?php

use Livewire\Component;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

new class extends Component
{
    #[On('cart_updated')]
    public function refreshBadge(): void
    {}

    public function getCount(): int
    {
        $cartService = app(CartService::class);

        if (Auth::check()) {
            $cart = $cartService->getCart();
            return $cart ? $cart->items()->count() : 0;
        }

        $sessionKey = $cartService->getSession();
        $cart = Session::get($sessionKey, []);

        return count($cart);
    }
};
?>

<span class="inline-flex items-center justify-center">
    @if(($count = $this->getCount()) > 0)
        <span class="absolute -top-1 right-0 inline-flex items-center justify-center min-w-[16px] h-4 px-1 text-[10px] font-bold leading-none text-gray-900 bg-green-400 rounded-full transform translate-x-1/2 -translate-y-1/4">
            {{ $count }}
        </span>
    @endif
</span>