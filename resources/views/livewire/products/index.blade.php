<?php

use App\Models\Product;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')]
class extends Component {
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $selectedCategory = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function with(): array
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['category'])
            ->whereHas('variants', function ($query) {
                $query->where('is_active', true);
            });

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return [
            'products' => $query->latest()->paginate(12),
            'categories' => Category::where('is_active', true)->get(),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:gap-8">
        
        <div class="w-full md:w-64 shrink-0 mb-8 md:mb-0">
            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm sticky top-6">
                <div class="mb-6">
                    <label for="search" class="block text-sm font-semibold text-gray-900 mb-2">Search</label>
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="search" id="search" type="text" placeholder="Product name..." 
                            class="p-1 w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <button wire:click="selectCategory(null)" 
                                class="w-full text-left text-sm py-1.5 px-2 rounded transition-colors {{ is_null($selectedCategory) ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                                All Categories
                            </button>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <button wire:click="selectCategory({{ $category->id }})" 
                                    class="w-full text-left text-sm py-1.5 px-2 rounded transition-colors {{ $selectedCategory === $category->id ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:cursor-pointer' }}">
                                    {{ $category->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex-1">
            @if($products->isEmpty())
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <p class="text-gray-500">No products found matching your search criteria.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <a href="/products/{{ $product->slug }}" wire:navigate 
                        class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between group hover:shadow-md transition-all cursor-pointer">
                            
                            <div>
                                <div class="aspect-square bg-gray-100 overflow-hidden relative">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            No image available
                                        </div>
                                    @endif
                                    <span class="absolute top-2 left-2 bg-gray-900/80 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">
                                        {{ $product->category->name }}
                                    </span>
                                </div>

                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition-colors">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-gray-500 text-xs mt-1 line-clamp-2">
                                        {{ Str::limit($product->description, 90) }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-4 pt-0 flex items-center justify-between mt-4">
                                <span class="text-lg font-bold text-gray-900">
                                    €{{ number_format($product->price / 100, 2, '.', ',') }}
                                </span>
                                
                                <span class="text-xs bg-gray-900 text-white font-medium px-3 py-2 rounded group-hover:bg-green-600 transition-colors">
                                    View Details
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8 [&_button]:cursor-pointer">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
</div>