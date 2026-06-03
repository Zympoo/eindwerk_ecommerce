<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')]
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
            $query->where('is_active', true);
        }]);

        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariantId = $this->product->variants->first()->id;
        }
    }

    public function getSelectedVariantProperty(): ?ProductVariant
    {
        return $this->product->variants->firstWhere('id', $this->selectedVariantId);
    }

    public function getCurrentPriceProperty(): int
    {
        $basePrice = $this->product->price;
        
        if ($this->selectedVariant) {
            $basePrice += $this->selectedVariant->additional_price;
        }

        return $basePrice;
    }

    public function addToCart(): void
    {
        $this->validate([
            'quantity' => 'required|integer|min:1',
            'selectedVariantId' => $this->product->variants->isNotEmpty() ? 'required|exists:product_variants,id' : 'nullable',
        ]);

        // TODO: Handle shopping cart logic (adding to session or database)...
        
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

    <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
        
        <div class="w-full aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400">
                    No image available
                </div>
            @endif
        </div>

        <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $product->name }}</h1>
            
            <div class="mt-3">
                <h2 class="sr-only">Product price</h2>
                <p class="text-3xl text-gray-900 font-bold">
                    €{{ number_format($this->currentPrice / 100, 2, '.', ',') }}
                </p>
            </div>

            <div class="mt-6">
                <h3 class="sr-only">Description</h3>
                <div class="text-base text-gray-700 space-y-6 leading-relaxed">
                    {{ $product->description }}
                </div>
            </div>

            <form wire:submit="addToCart" class="mt-6 border-t border-gray-200 pt-6">
                
                @if($product->variants->isNotEmpty())
                    <div class="mb-6">
                        <label for="variant" class="block text-sm font-semibold text-gray-900 mb-2">Choose an option</label>
                        <select wire:model.live="selectedVariantId" id="variant" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }}">
                                    {{ $variant->name }} 
                                    @if($variant->additional_price > 0)
                                        (+ €{{ number_format($variant->additional_price / 100, 2, '.', ',') }})
                                    @endif
                                    ({{ $variant->stock > 0 ? 'Stock: ' . $variant->stock : 'Out of stock' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mb-6 flex items-center gap-4">
                    <div class="w-24">
                        <label for="quantity" class="block text-sm font-semibold text-gray-900 mb-2">Quantity</label>
                        <input wire:model="quantity" id="quantity" type="number" min="1" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                    </div>

                    <div class="flex-1 pt-7">
                        @if($product->variants->isNotEmpty() && $this->selectedVariant && $this->selectedVariant->stock <= 0)
                            <button type="button" disabled 
                                class="w-full bg-gray-300 text-gray-500 text-sm font-medium py-3 px-8 rounded-md cursor-not-allowed text-center">
                                Out of Stock
                            </button>
                        @else
                            <button type="submit" 
                                class="w-full bg-green-600 text-white text-sm font-medium py-3 px-8 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors text-center">
                                Add to Cart
                            </button>
                        @endif
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>