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

<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/products" wire:navigate class="text-sm text-gray-500 hover:text-green-600 flex items-center gap-1">
            ← Back to overview
        </a>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-md text-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="lg:grid lg:grid-cols-2 lg:gap-x-8">
        <div class="w-full aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400">No image</div>
            @endif
        </div>

        <div class="mt-10 lg:mt-0">
            <h1 class="text-3xl font-extrabold text-gray-900">{{ $product->name }}</h1>
            
            <div class="mt-3">
                <p class="text-3xl text-gray-900 font-bold">
                    €{{ number_format($this->currentPrice / 100, 2, '.', ',') }}
                </p>
            </div>

            <div class="mt-6">
                <div class="text-base text-gray-700 leading-relaxed">
                    {{ $product->description }}
                </div>
            </div>

            <form wire:submit="addToCart" class="mt-6 border-t border-gray-200 pt-6">
                @if($product->variants->isNotEmpty())
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Choose an option</label>
                        <select wire:model.live="selectedVariantId" 
                            class="px-3 py-2 w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
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

                <div class="mb-6 flex items-center gap-4">
                    <div class="w-24">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Quantity</label>
                        <input 
                            wire:model="quantity" 
                            type="number" 
                            min="1" 
                            max="{{ $this->selectedVariant ? $this->selectedVariant->stock : 1 }}"
                            class="px-3 py-2 w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
                        >
                    </div>

                    <div class="flex-1 pt-7">
                        @if($this->selectedVariant && $this->selectedVariant->stock <= 0)
                            <button type="button" disabled class="w-full bg-gray-300 text-gray-500 py-3 rounded-md cursor-not-allowed">Out of Stock</button>
                        @else
                            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 transition-colors font-bold">
                                Add to Cart
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($this->comparableProducts->isNotEmpty())
        <div class="mt-16 border-t border-gray-200 pt-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Comparable Products</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                @foreach($this->comparableProducts as $compProduct)
                    <a href="/products/{{ $compProduct->slug }}" wire:navigate class="group block text-sm">
                        <div class="w-full aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 group-hover:opacity-75 transition-opacity">
                            @if($compProduct->image_path)
                                <img src="{{ asset('storage/' . $compProduct->image_path) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No image</div>
                            @endif
                        </div>
                        <h3 class="mt-3 font-semibold text-gray-900 group-hover:text-green-600 transition-colors line-clamp-1">
                            {{ $compProduct->name }}
                        </h3>
                        <p class="text-gray-500 text-xs mb-1">{{ $compProduct->category->name }}</p>
                        <p class="text-gray-900 font-bold">
                            €{{ number_format($compProduct->price / 100, 2, '.', ',') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>