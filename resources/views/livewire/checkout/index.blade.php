<?php

use App\Actions\Orders\CreateOrderAction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Services\CartService;

new #[Layout('components.layouts.app')]
class extends Component {

    public array $address = [];

    public function mount()
    {
        if (Auth::check()) {
            $this->address['name'] = Auth::user()->name;
            $this->address['email'] = Auth::user()->email;
        }
    }

    #[Computed]
    public function items()
    {
        return app(CartService::class)->getItems();
    }

    #[Computed]
    public function total()
    {
        return $this->items->reduce(function ($carry, $item) {
            $price = $item->product->price + ($item->variant ? $item->variant->additional_price : 0);
            return $carry + ($price * $item->quantity);
        }, 0);
    }

    public function checkout(CreateOrderAction $action)
    {
        $this->validate([
            'address.name' => 'required|string',
            'address.email' => 'required|email',
            'address.street' => 'required|string',
            'address.city' => 'required|string',
            'address.postal_code' => 'required|string',
            'address.country' => 'required|string',
        ], [
            'address.name.required' => 'Please enter your name so we can process your order.',
            'address.email.required' => 'Your email address is required to send the order confirmation.',
            'address.email.email' => 'Please provide a valid email address.',
            'address.street.required' => 'Street and house number are required for delivery.',
            'address.city.required' => 'Please fill in your city.',
            'address.postal_code.required' => 'Postal code is required for shipping.',
            'address.country.required' => 'Please choose your country for delivery.',
        ]);

        if ($this->items->isEmpty()) {
            $this->addError('cart', 'Your shopping cart is empty.');
            return;
        }

        foreach ($this->items as $item) {
            if ($item->variant) {
                if ($item->variant->stock < $item->quantity) {
                    $this->addError('cart', sprintf(
                        'Sorry, there are only %d units left in stock for "%s (%s)". Please reduce your quantity.',
                        $item->variant->stock,
                        $item->product->name,
                        $item->variant->name
                    ));
                    return;
                }
            }
        }

        $cartArray = $this->items->map(function($item) {
            $finalPrice = $item->product->price + ($item->variant ? $item->variant->additional_price : 0);
            $fullName = $item->product->name . ($item->variant ? ' (' . $item->variant->name . ')' : '');

            return [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name' => $fullName,
                'price' => $finalPrice,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $url = $action->handle(
            $cartArray,
            $this->address,
            Auth::id()
        );

        return redirect()->away($url);
    }
};
?>

<div class="min-h-screen bg-white py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-12">
            <span class="tech-label text-mongo-dark-green mb-2 block">Checkout</span>
            <h2 class="text-[36px] font-medium leading-tight">
                Complete your <span class="mongo-underline">order</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            <div class="space-y-6">
                <h3 class="text-[24px] font-medium">Shipping Address</h3>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-600 p-4 rounded mb-4">
                        <ul class="list-disc ml-5 text-sm space-y-1">
                            @foreach ($errors->getBags()['default']->messages() as $key => $messages)
                                @foreach ($messages as $error)
                                        <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <input wire:model="address.name"  type="text" placeholder="Full Name" class="input w-full rounded-lg border border-silver-teal bg-white px-4 py-3 text-sm focus:border-mongo-dark-green focus:ring-2 focus:ring-mongo-dark-green/20">
                <input wire:model="address.email" type="email" placeholder="Email Address" class="input w-full rounded-lg border border-silver-teal bg-white px-4 py-3 text-sm focus:border-mongo-dark-green focus:ring-2 focus:ring-mongo-dark-green/20">
                <input wire:model="address.street" type="text" placeholder="Street and House Number" class="input w-full rounded-lg border border-silver-teal bg-white px-4 py-3 text-sm focus:border-mongo-dark-green focus:ring-2 focus:ring-mongo-dark-green/20">
                <input wire:model="address.city" type="text" placeholder="City" class="input w-full rounded-lg border border-silver-teal bg-white px-4 py-3 text-sm focus:border-mongo-dark-green focus:ring-2 focus:ring-mongo-dark-green/20">
                <input wire:model="address.postal_code" type="text" placeholder="Postal Code" class="input w-full rounded-lg border border-silver-teal bg-white px-4 py-3 text-sm focus:border-mongo-dark-green focus:ring-2 focus:ring-mongo-dark-green/20">
                <input wire:model="address.country" type="text" placeholder="Country" class="input w-full rounded-lg border border-silver-teal bg-white px-4 py-3 text-sm focus:border-mongo-dark-green focus:ring-2 focus:ring-mongo-dark-green/20">
            </div>

            <div class="bg-light-input border border-silver-teal rounded-[16px] p-6">
                <h3 class="text-[24px] font-medium mb-6">Your Order</h3>

                <div class="space-y-4">
                    @forelse($this->items as $item)
                        @php
                            $itemPrice = $item->product->price + ($item->variant ? $item->variant->additional_price : 0);
                        @endphp
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">{{ $item->product->name }}</p>
                                @if($item->variant)
                                    <p class="text-xs text-action-blue">Variant: {{ $item->variant->name }}</p>
                                @endif
                                <p class="text-sm text-cool-gray">
                                    {{ $item->quantity }} x €{{ number_format($itemPrice / 100, 2, '.', ',') }}
                                </p>
                            </div>
                            <span>
                                €{{ number_format(($itemPrice * $item->quantity) / 100, 2, '.', ',') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-cool-gray">Your shopping cart is empty.</p>
                    @endforelse
                </div>

                <div class="border-t border-silver-teal mt-6 pt-4 flex justify-between font-medium text-lg">
                    <span>Total</span>
                    <span>€{{ number_format($this->total / 100, 2, '.', ',') }}</span>
                </div>

                <button
                    wire:click="checkout"
                    class="mt-6 w-full bg-mongo-dark-green text-white py-3 rounded hover:opacity-90 hover:cursor-pointer font-bold"
                >
                    Proceed to Payment
                </button>
            </div>

        </div>
    </div>
</div>