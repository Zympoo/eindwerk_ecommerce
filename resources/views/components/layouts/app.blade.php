<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tendens</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased text-black bg-white">

    <header class="bg-gray-900 text-white pt-6 pb-6 px-4 sm:px-6 lg:px-8 border-b border-gray-700">
        <input type="checkbox" id="menu-toggle" class="hidden peer" />

        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4 peer-checked:[&>nav]:flex">
            
            <div class="flex items-center justify-between w-full md:w-auto">
                <a href="/" class="text-mongo-green font-serif text-2xl font-bold tracking-tight">
                    Tendens
                </a>

                <label for="menu-toggle" class="block md:hidden text-white hover:text-green-400 cursor-pointer select-none p-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
            </div>

            <nav class="hidden md:flex flex-col md:flex-row gap-4 md:gap-8 font-medium text-[16px] items-start md:items-center w-full md:w-auto mt-4 md:mt-0 pt-4 md:pt-0 border-t border-gray-800 md:border-t-0">
                
                <a href="/products" class="text-white hover:text-green-400 transition-colors w-full md:w-auto py-1">Products</a>
                
                <a href="/cart" class="relative inline-flex items-center pr-4 text-white hover:text-green-400 transition-colors w-full md:w-auto py-1"
                   x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                    <span>Cart</span>
                    <livewire:cart.badge />
                    <div class="hidden md:block">
                        <livewire:cart.dropdown />
                    </div>
                </a>

                @auth
                    <a href="/orders" class="text-white hover:text-green-400 transition-colors w-full md:w-auto py-1">My orders</a>
                    @if(auth()->user()->isAdmin())
                        <a href="/admin" class="text-white hover:text-green-400 transition-colors w-full md:w-auto py-1">
                            Dashboard
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="w-full md:w-auto">
                        @csrf
                        <button type="submit" class="text-white hover:text-green-400 hover:cursor-pointer transition-colors w-full text-left md:w-auto py-1">Logout</button>
                    </form>
                @else
                    <a href="/login" class="text-white hover:text-green-400 transition-colors w-full md:w-auto py-1">Login</a>
                    <a href="/register" class="text-white hover:text-green-400 transition-colors w-full md:w-auto py-1">Register</a>
                @endauth

            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>