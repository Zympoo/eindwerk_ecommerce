<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')]
class extends Component {
    
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our data.'),
            ]);
        }

        session()->regenerate();

        if (Auth::user()->isAdmin()) {
            $this->redirect('/admin');
            return;
        }

        $this->redirect('/', navigate: true);
    }
};
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto w-full max-w-md">
        <h2 class="text-center text-3xl font-extrabold text-green-600">
            Login
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto w-full max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-200">
            <form wire:submit="login" class="space-y-6">
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email adres</label>
                    <input wire:model="email" id="email" type="email" required 
                        class="p-1 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    @error('email') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Wachtwoord</label>
                    <input wire:model="password" id="password" type="password" required 
                        class="p-1 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    @error('password') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input wire:model="remember" id="remember" type="checkbox" 
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="hover:cursor-pointer w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Log in
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <a href="/register" class="text-sm text-green-600 hover:underline">Register</a>
            </div>
        </div>
    </div>
</div>