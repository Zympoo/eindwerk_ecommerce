<x-layouts.app>
    <div class="min-h-screen bg-white py-16">
        <div class="max-w-3xl mx-auto text-center">

            <span class="tech-label text-green-600 mb-2 block uppercase tracking-widest text-xs font-bold">
                Payment Successful
            </span>

            <h2 class="text-[36px] font-medium mb-6">
                Thank you for your <span class="mongo-underline">order!</span>
            </h2>

            <div class="bg-gray-50 border border-silver-teal rounded-2xl p-8 mb-8 inline-block min-w-[320px]">
                <p class="text-cool-gray mb-2">
                    Order number: <strong class="text-gray-900">{{ $order->order_number }}</strong>
                </p>

                <p class="text-cool-gray">
                    Total amount: <strong class="text-gray-900">€{{ number_format($order->total_price / 100, 2, '.', ',') }}</strong>
                </p>
            </div>

            <div class="flex justify-center gap-4">
                <a href="/products" wire:navigate
                   class="bg-mongo-dark-green text-white px-8 py-3 rounded-full font-bold hover:opacity-90 transition shadow-lg">
                    Continue Shopping
                </a>

                <a href="/orders" wire:navigate
                   class="border border-silver-teal text-mongo-dark-green px-8 py-3 rounded-full font-bold hover:border-action-blue hover:text-action-blue transition">
                    My Orders
                </a>
            </div>

        </div>
    </div>
</x-layouts.app>