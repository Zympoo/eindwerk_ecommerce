<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app')]
class extends Component {

    public Order $order;

    public function mount(Order $order)
    {
        $this->authorize('view', $order);

        $this->order = $order->load(['items.product', 'items.variant']);
    }
};
?>

<div class="min-h-screen bg-white py-16">
    <div class="max-w-4xl mx-auto px-4">

        <div class="mb-6">
            <a href="/orders"
               class="text-action-blue hover:underline">
                ← Back to my orders
            </a>
        </div>

        <div class="mb-12">
            <span class="tech-label text-mongo-dark-green mb-2 block">
                Order Details
            </span>

            <h2 class="text-[36px] font-medium">
                Order <span class="mongo-underline">#{{ $order->order_number }}</span>
            </h2>
        </div>

        @if($order->status === 'pending')
            <div class="bg-light-input border border-silver-teal rounded-[16px] p-6 mb-8 flex items-center justify-between">

                <div>
                    <p class="font-medium text-[18px]">
                        This order has not been paid yet
                    </p>

                    <p class="text-cool-gray text-sm mt-1">
                        Complete your payment to confirm your order.
                    </p>
                </div>

                <form method="POST" action="{{ route('orders.pay', $order) }}">
                    @csrf
                    <button
                        class="bg-action-blue text-white px-6 py-3 rounded hover:opacity-90 transition-colors font-medium hover:cursor-pointer"
                    >
                        Pay Now
                    </button>
                </form>

            </div>
        @endif

        <div class="bg-light-input border border-silver-teal rounded-[16px] p-6 mb-8">
            <p class="text-cool-gray">
                Date: {{ $order->created_at->format('Y-m-d H:i') }}
            </p>

            <p class="text-cool-gray mt-1">
                Status: <span class="font-medium">{{ ucfirst($order->status) }}</span>
            </p>

            <p class="font-medium mt-3 text-lg">
                Total: €{{ number_format($order->total_price / 100, 2, '.', ',') }}
            </p>
        </div>

        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex justify-between border border-silver-teal rounded-[12px] p-4 bg-white shadow-sm">

                    <div>
                        <p class="font-medium">
                            {{ $item->product->name ?? 'Removed Product' }}
                        </p>
                        @if($item->variant)
                            <p class="text-xs text-action-blue font-medium">Variant: {{ $item->variant->name }}</p>
                        @endif

                        <p class="text-sm text-cool-gray mt-1">
                            {{ $item->quantity }} x €{{ number_format($item->price / 100, 2, '.', ',') }}
                        </p>
                    </div>

                    <span class="font-medium">
                        €{{ number_format(($item->price * $item->quantity) / 100, 2, '.', ',') }}
                    </span>

                </div>
            @endforeach
        </div>

    </div>
</div>