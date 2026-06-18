<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Actions\Cart\AddItemToCartAction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Layout('components.layouts.app')]
class extends Component {
    public Product $product;
    public ?int $selectedVariantId = null;
    public int $quantity = 1;

    public function mount(Product $product): void
    {
        if (!$product->is_active) {
            abort(404);
        }

        $this->product = $product->load(['variants' => function($query) {
            $query->where('is_active', true)->orderBy('additional_price', 'asc');
        }]);

        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariantId = $this->product->variants->first()->id;
        }
    }

    #[Computed]
    public function comparableProducts()
    {
        return Product::query()
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('is_active', true)
            ->whereHas('variants', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category'])
            ->inRandomOrder()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function selectedVariant(): ?ProductVariant
    {
        return $this->product->variants->firstWhere('id', $this->selectedVariantId);
    }

    #[Computed]
    public function currentPrice(): int
    {
        $price = $this->product->price;
        if ($this->selectedVariant) {
            $price += $this->selectedVariant->additional_price;
        }
        return $price;
    }

    public function addToCart(AddItemToCartAction $action): void
    {
        $maxStock = $this->selectedVariant ? $this->selectedVariant->stock : 0;

        $this->validate([
            'quantity' => "required|integer|min:1|max:{$maxStock}",
            'selectedVariantId' => $this->product->variants->isNotEmpty() ? 'required|exists:product_variants,id' : 'nullable',
        ], [
            'quantity.max' => "You cannot add more than {$maxStock} items because that is all we have in stock."
        ]);

        $action->handle($this->product, $this->quantity, $this->selectedVariantId);

        session()->flash('message', 'Product successfully added to your cart!');
        
        $this->redirect('/cart', navigate: true);
    }
};
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 max-h-screen flex flex-col justify-between">
    <div class="mb-4">
        <a href="/products" wire:navigate class="text-xs text-gray-500 hover:text-green-600 flex items-center gap-1">
            ← Back to overview
        </a>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-md text-xs">
            {{ session('message') }}
        </div>
    @endif

    <div class="lg:grid lg:grid-cols-12 lg:gap-x-6 items-start">
        <div class="lg:col-span-5 w-full aspect-[4/3] lg:max-h-[320px] bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No image</div>
            @endif
        </div>

        <div class="mt-6 lg:mt-0 lg:col-span-7 flex flex-col justify-between h-full">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $product->name }}</h1>
                
                <div class="mt-2">
                    <p class="text-2xl text-gray-900 font-bold">
                        €{{ number_format($this->currentPrice / 100, 2, '.', ',') }}
                    </p>
                </div>

                <div class="mt-3">
                    <div class="text-sm text-gray-600 leading-relaxed line-clamp-3" title="{{ $product->description }}">
                        {{ $product->description }}
                    </div>
                </div>
            </div>

            <form wire:submit="addToCart" class="mt-4 border-t border-gray-150 pt-4">
                @if($product->variants->isNotEmpty())
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-900 mb-1">Choose an option</label>
                        <select wire:model.live="selectedVariantId" 
                            class="px-2 py-1.5 max-w-md w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }}">
                                    {{ $variant->name }} 
                                    @if($variant->additional_price > 0) (+ €{{ number_format($variant->additional_price / 100, 2) }}) @endif
                                    ({{ $variant->stock > 0 ? 'In stock' : 'Out of stock' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-4">
                    <div class="w-20">
                        <label class="block text-xs font-semibold text-gray-900 mb-1">Quantity</label>
                        <input 
                            wire:model="quantity" 
                            type="number" 
                            min="1" 
                            max="{{ $this->selectedVariant ? $this->selectedVariant->stock : 1 }}"
                            class="px-2 py-1.5 w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-xs"
                        >
                    </div>

                    <div class="max-w-md">
                        @if($this->selectedVariant && $this->selectedVariant->stock <= 0)
                            <button type="button" disabled class="w-full bg-gray-300 text-gray-500 py-2 rounded-md cursor-not-allowed text-sm font-semibold">
                                Out of Stock
                            </button>
                        @else
                            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition-colors text-sm font-bold shadow-sm">
                                Add to Cart
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($this->comparableProducts->isNotEmpty())
        <div class="mt-8 border-t border-gray-200 pt-4">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Comparable Products</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($this->comparableProducts as $compProduct)
                    <a href="/products/{{ $compProduct->slug }}" wire:navigate class="group block text-xs">
                        <div class="w-full aspect-[4/3] bg-gray-100 rounded-md overflow-hidden border border-gray-200 group-hover:opacity-75 transition-opacity">
                            @if($compProduct->image_path)
                                <img src="{{ asset('storage/' . $compProduct->image_path) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-[10px]">No image</div>
                            @endif
                        </div>
                        <h3 class="mt-1.5 font-semibold text-gray-900 group-hover:text-green-600 transition-colors line-clamp-1">
                            {{ $compProduct->name }}
                        </h3>
                        <p class="text-gray-900 font-bold mt-0.5">
                            €{{ number_format($compProduct->price / 100, 2, '.', ',') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>