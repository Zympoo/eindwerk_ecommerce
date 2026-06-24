<?php

use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Models\CartItem;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app')]
class extends Component {

    public function updateQuantity(UpdateCartItemAction $action, $itemId, $quantity)
    {
        $cartItem = CartItem::find($itemId);
        
        if ($cartItem && $cartItem->variant) {
            $maxStock = $cartItem->variant->stock;
            
            if ((int)$quantity > $maxStock) {
                $quantity = $maxStock;
                $this->addError('stock_' . $itemId, "Only {$maxStock} items are currently available in stock.");
            }
        }

        $action->handle($itemId, (int)$quantity);
    }

    public function removeItem(RemoveCartItemAction $action, $itemId)
    {
        $action->handle($itemId);
    }

    #[Computed]
    public function cartItems()
    {
        return app(CartService::class)->getItems();
    }

    #[Computed]
    public function total()
    {
        return $this->cartItems->reduce(function ($carry, $item) {
            $price = $item->product->price + ($item->variant ? $item->variant->additional_price : 0);
            return $carry + ($price * $item->quantity);
        }, 0);
    }
};
?>

<div class="min-h-screen bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-12">
            <span class="tech-label text-mongo-dark-green mb-2 block">Shopping Cart</span>
            <h2 class="text-[36px] font-medium leading-tight">
                Your <span class="mongo-underline">Cart</span>
            </h2>
        </div>

        <div class="space-y-6">

            @forelse($this->cartItems as $item)
                @php
                    $itemPrice = $item->product->price + ($item->variant ? $item->variant->additional_price : 0);
                    $maxStock = $item->variant ? $item->variant->stock : 1;
                @endphp
                <div class="bg-white border border-silver-teal rounded-[16px] shadow-forest p-6 flex items-center justify-between">

                    <div>
                        <h3 class="text-[20px] font-medium">
                            {{ $item->product->name }}
                        </h3>
                        @if($item->variant)
                            <p class="text-sm text-action-blue font-medium mb-1">Variant: {{ $item->variant->name }}</p>
                        @endif

                        <p class="text-cool-gray">
                            €{{ number_format($itemPrice / 100, 2, '.', ',') }}
                        </p>

                        @error('stock_' . $item->id)
                            <span class="text-xs text-red-500 font-medium mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">

                        <input
                            type="number"
                            min="1"
                            max="{{ $maxStock }}"
                            value="{{ $item->quantity }}"
                            wire:change="updateQuantity('{{ $item->id }}', $event.target.value)" 
                            class="border border-silver-teal rounded px-3 py-2 w-20"
                        />

                        <button
                            wire:click="removeItem('{{ $item->id }}')" 
                            class="text-red-500 font-bold hover:underline"
                        >
                            Remove
                        </button>

                    </div>
                </div>
            @empty
                <p class="text-center text-cool-gray py-12">
                    Your shopping cart is empty.
                </p>
            @endforelse

        </div>

        <div class="mt-10 border-t border-silver-teal pt-6 text-right">
            <div class="inline-block min-w-[200px]">
                <p class="text-[22px] font-medium text-gray-900">
                    Total: <span class="font-bold">€{{ number_format($this->total / 100, 2, '.', ',') }}</span>
                </p>

                <div class="flex justify-center gap-4">
                    <a href="/products" wire:navigate
                       class="mt-4 inline-block text-center bg-mongo-dark-green hover:bg-opacity-90 text-white font-bold text-sm px-8 py-3 rounded-full shadow-md transition-all duration-200">
                        Continue Shopping
                    </a>
                    <div class="mt-4">
                        @if($this->cartItems->isNotEmpty())
                            <a
                                href="/checkout"
                                wire:navigate
                                class="inline-block text-center bg-mongo-dark-green hover:bg-opacity-90 text-white font-bold text-sm px-8 py-3 rounded-full shadow-md transition-all duration-200"
                            >
                                Proceed to Checkout
                            </a>
                        @else
                            <button
                                disabled
                                class="inline-block text-center bg-gray-300 text-gray-500 font-bold text-sm px-8 py-3 rounded-full cursor-not-allowed"
                            >
                                Proceed to Checkout
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>