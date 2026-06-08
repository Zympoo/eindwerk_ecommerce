<?php

use Livewire\Component;
use App\Services\CartService;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

new class extends Component
{
    #[On('cart_updated')]
    public function refreshDropdown(): void
    {}

    protected function getCartData(): array
    {
        $cartService = app(CartService::class);

        if (Auth::check()) {
            $cart = $cartService->getCart();
            if (!$cart) return [];

            return $cart->items->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'variant' => $item->variant?->name ?? null, 
                    'quantity' => $item->quantity,
                ];
            })->toArray();
        }

        $sessionKey = $cartService->getSession();
        $sessionCart = Session::get($sessionKey, []);

        $items = [];
        foreach ($sessionCart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $variant = $item['product_variant_id'] 
                ? ProductVariant::find($item['product_variant_id'])?->name 
                : null;

            $items[] = [
                'name' => $product->name,
                'variant' => $variant,
                'quantity' => $item['quantity'],
            ];
        }

        return $items;
    }

    public function with(): array
    {
        $items = $this->getCartData();

        return [
            'count' => count($items),
            'items' => $items,
        ];
    }
};
?>

@if($count > 0)
    <div 
        x-show="open"
        class="absolute right-0 top-full mt-2 w-64 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50 p-3 text-sm text-white pointer-events-none"
        x-cloak
    >
        <ul class="divide-y divide-gray-700 max-h-60 overflow-y-auto">
            @foreach($items as $item)
                <li class="py-2 flex justify-between items-start gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-white font-medium">{{ $item['name'] }}</p>
                        @if($item['variant'])
                            <p class="text-xs text-gray-400">{{ $item['variant'] }}</p>
                        @endif
                    </div>
                    <span class="text-xs bg-gray-700 px-1.5 py-0.5 rounded text-gray-300 whitespace-nowrap">
                        {{ $item['quantity'] }}x
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif