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

    public function cancelOrder()
    {
        $this->authorize('update', $this->order);

        if ($this->order->status !== 'pending') {
            session()->flash('error', 'Only pending orders can be cancelled.');
            return;
        }

        $this->order->update([
            'status' => 'cancelled'
        ]);

        session()->flash('message', 'Your order has been successfully cancelled.');
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

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-[12px] flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm font-medium">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        @endif

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-[12px] flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm font-medium">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        @endif

        <div class="mb-12">
            <span class="tech-label text-mongo-dark-green mb-2 block">
                Order Details
            </span>

            <h2 class="text-[36px] font-medium">
                Order <span class="mongo-underline">#{{ $order->order_number }}</span>
            </h2>
        </div>

        @if($order->status === 'pending')
            <div class="bg-light-input border border-silver-teal rounded-[16px] p-6 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div>
                    <p class="font-medium text-[18px]">
                        This order has not been paid yet
                    </p>

                    <p class="text-cool-gray text-sm mt-1">
                        Complete your payment to confirm your order or cancel it.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- De nieuwe Annuleer-knop --}}
                    <button 
                        wire:click="cancelOrder"
                        wire:confirm="Are you sure you want to cancel this order?"
                        class="text-red-600 hover:text-red-700 font-medium px-4 py-3 rounded transition-colors hover:cursor-pointer"
                    >
                        Cancel Order
                    </button>

                    <form method="POST" action="{{ route('orders.pay', $order) }}">
                        @csrf
                        <button
                            class="bg-action-blue text-white px-6 py-3 rounded hover:opacity-90 transition-colors font-medium hover:cursor-pointer shadow-sm"
                        >
                            Pay Now
                        </button>
                    </form>
                </div>

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