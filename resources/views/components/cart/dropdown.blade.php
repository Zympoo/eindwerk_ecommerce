<?php

use Livewire\Component;
use App\Services\CartService;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component
{
    #[On('cart_updated')]
    public function refreshDropdown(): void
    {
        // Deze methode dwingt Livewire om de component en 
        // dus de getCartData() hieronder opnieuw te berekenen.
    }

    protected function getCartData(): array
    {
        $cartItems = app(CartService::class)->getItems();
        
        if (!$cartItems) {
            return [];
        }

        return $cartItems->map(function ($item) {
            $price = $item->product->price + ($item->variant ? $item->variant->additional_price : 0);
            
            return [
                'name'     => $item->product->name,
                'variant'  => $item->variant?->name ?? null, 
                'quantity' => $item->quantity,
                'price'    => $price,
            ];
        })->toArray();
    }

    public function with(): array
    {
        $items = $this->getCartData();
        
        $totalPrice = collect($items)->reduce(function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        return [
            'count'      => count($items),
            'items'      => $items,
            'totalPrice' => $totalPrice,
        ];
    }
};
?>

<div class="absolute right-0 top-full mt-2 z-50" x-show="open" x-cloak>
    @if($count > 0)
        <div 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            class="w-72 bg-gray-800 border border-gray-700 rounded-lg shadow-xl p-3 text-sm text-white pointer-events-auto"
        >
            <ul class="divide-y divide-gray-700 max-h-60 overflow-y-auto pr-1">
                @foreach($items as $item)
                    <li class="py-2.5 flex justify-between items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-white font-medium">{{ $item['name'] }}</p>
                            @if($item['variant'])
                                <p class="text-[11px] text-green-400 font-medium">{{ $item['variant'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">
                                €{{ number_format(($item['price'] * $item['quantity']) / 100, 2, '.', ',') }}
                            </p>
                        </div>
                        
                        <span class="text-xs bg-gray-700 px-1.5 py-0.5 rounded text-gray-300 font-mono whitespace-nowrap">
                            {{ $item['quantity'] }}x
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="pt-2.5 border-t border-gray-700 mt-2 flex justify-between items-center text-xs">
                <span class="text-gray-400">Total:</span>
                <span class="font-bold text-white text-sm">€{{ number_format($totalPrice / 100, 2, '.', ',') }}</span>
            </div>
        </div>
    @endif
</div>