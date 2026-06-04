<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;
use Livewire\Attributes\Computed;

new #[Layout('components.layouts.app')]
class extends Component {
    use WithPagination;

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
    }
};
?>

<div class="min-h-screen bg-white py-16">
    <div class="max-w-5xl mx-auto px-4">

        <div class="mb-12">
            <span class="tech-label text-mongo-dark-green mb-2 block">Orders</span>
            <h2 class="text-[36px] font-medium">
                My <span class="mongo-underline">Orders</span>
            </h2>
        </div>

        <div class="space-y-4">
            @forelse($this->orders as $order)
                <a href="/orders/{{ $order->id }}"
                   class="block border border-silver-teal rounded-[16px] p-6 hover:border-action-blue transition">

                    <div class="flex justify-between">
                        <div>
                            <p class="font-medium">
                                Order #{{ $order->order_number }}
                            </p>
                            <p class="text-sm text-cool-gray">
                                {{ $order->created_at->format('Y-m-d') }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="font-medium">
                                €{{ number_format($order->total_price / 100, 2, '.', ',') }}
                            </p>

                            <span class="text-sm px-2 py-1 rounded text-capitalize font-medium 
                                {{ match($order->status) {
                                    'paid' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800',
                                } }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                </a>
            @empty
                <p class="text-cool-gray text-center py-12">
                    You have not placed any orders yet.
                </p>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $this->orders->links() }}
        </div>

    </div>
</div>