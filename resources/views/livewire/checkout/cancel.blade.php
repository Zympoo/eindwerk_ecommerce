<x-layouts.app>
    <div class="min-h-screen bg-white py-16">
        <div class="max-w-3xl mx-auto text-center">

        <span class="tech-label text-red-500 mb-2 block">
            Payment cancelled
        </span>

            <h2 class="text-[36px] font-medium mb-6">
                Your payment was not completed
            </h2>

            <p class="text-cool-gray mb-8">
                Don't worry, your order has not been finalized.
                You can try again or update your shopping cart.
            </p>

            <div class="flex justify-center gap-4">

                <a href="/cart"
                   class="bg-mongo-dark-green text-white px-6 py-3 rounded">
                    Return to cart
                </a>

                <a href="/products"
                   class="border border-silver-teal px-6 py-3 rounded">
                    Continue shopping
                </a>

            </div>

        </div>
    </div>
</x-layouts.app>