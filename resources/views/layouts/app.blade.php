<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MarketGo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600">MarketGo</a>
                    <p class="text-sm text-slate-500">Tu asistente inteligente para las compras del hogar.</p>
                </div>
                <nav class="flex flex-wrap items-center gap-3 text-sm font-medium text-slate-600">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : '' }}">Dashboard</a>
                    <a href="{{ route('shopping-lists.index') }}" class="hover:text-indigo-600 {{ request()->routeIs('shopping-lists.index') || request()->routeIs('shopping-lists.show') ? 'text-indigo-600' : '' }}">Listas de compra</a>
                    <a href="{{ route('shopping-lists.create') }}" class="hover:text-indigo-600 {{ request()->routeIs('shopping-lists.create') ? 'text-indigo-600' : '' }}">Crear lista</a>
                    <a href="{{ route('products.index') }}" class="hover:text-indigo-600 {{ request()->routeIs('products.*') ? 'text-indigo-600' : '' }}">Productos</a>
                    <a href="{{ route('supermarkets.index') }}" class="hover:text-indigo-600 {{ request()->routeIs('supermarkets.*') ? 'text-indigo-600' : '' }}">Establecimientos</a>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600" target="_blank">Admin</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="ml-2 inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-500">Salir</button>
                        </form>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @if(session('status'))
                    <div class="mb-6 rounded-md bg-green-100 border border-green-200 p-4 text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-md bg-red-100 border border-red-200 p-4 text-red-800">
                        <p class="font-semibold mb-2">Ups, revisa la información:</p>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="bg-white border-t border-slate-200 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs text-slate-500">
                &copy; {{ date('Y') }} MarketGo. Construido para optimizar tus compras inteligentes.
            </div>
        </footer>
    </div>

    @stack('modals')
</body>
</html>
