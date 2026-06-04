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
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="/" class="text-mongo-green font-serif text-2xl font-bold tracking-tight">
                Tendens
            </a>

            <div class="flex items-center gap-8">
                <nav class="hidden md:flex gap-8 font-medium text-[16px] items-center">

                    <a href="/products" class="text-white hover:text-green-400 transition-colors">Products</a>
                    <a href="/cart" class="text-white hover:text-green-400 transition-colors">Cart</a>

                    @auth
                        <a href="/orders" class="text-white hover:text-green-400 transition-colors">My orders</a>
                        @if(auth()->user()->isAdmin())
                            <a href="/admin" class="text-white hover:text-green-400 transition-colors">
                                Dashboard
                            </a>
                        @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-white hover:text-green-400 hover:cursor-pointer transition-colors">Uitloggen</button>
                            </form>
                    @else
                        <a href="/login" class="text-white hover:text-green-400 transition-colors">Inloggen</a>
                        <a href="/register" class="text-white hover:text-green-400 transition-colors">Registreren</a>
                    @endauth

                </nav>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
