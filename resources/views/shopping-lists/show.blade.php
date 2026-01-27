@extends('layouts.app')

@section('title', 'Lista: ' . $shoppingList->name)

@php
    $totalItems = $shoppingList->items->count();
    $pendingCount = $shoppingList->items->where('status', 'pending')->count();
    $inCartCount = $shoppingList->items->where('status', 'in_cart')->count();
    $estimatedTotal = $shoppingList->estimated_total ?? 0;
    $budget = $shoppingList->budget ?? 0;
@endphp

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $shoppingList->name }}</h1>
            <p class="text-sm text-slate-500">{{ optional($shoppingList->supermarket)->name ?? 'Sin establecimiento principal' }}</p>
            <div class="mt-3 flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $shoppingList->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                    {{ $shoppingList->status === 'active' ? 'Lista activa' : 'Lista inactiva' }}
                </span>
                <form method="POST" action="{{ route('shopping-lists.update', $shoppingList) }}" class="inline-flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $shoppingList->status === 'active' ? 'draft' : 'active' }}">
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">
                        {{ $shoppingList->status === 'active' ? 'Desactivar lista' : 'Activar lista' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('shopping-lists.destroy', $shoppingList) }}" class="inline-flex items-center gap-2" onsubmit="return confirm('¿Eliminar esta lista de forma permanente?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50">Eliminar lista</button>
                </form>
            </div>
        </div>
        <a href="{{ route('shopping-lists.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">← Volver a todas las listas</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <h2 class="text-xs uppercase text-slate-500">Productos totales</h2>
            <p class="text-2xl font-semibold text-slate-800">{{ $totalItems }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <h2 class="text-xs uppercase text-slate-500">Pendientes</h2>
            <p class="text-2xl font-semibold text-indigo-600">{{ $pendingCount }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <h2 class="text-xs uppercase text-slate-500">En el carrito</h2>
            <p class="text-2xl font-semibold text-emerald-600">{{ $inCartCount }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <h2 class="text-xs uppercase text-slate-500">Presupuesto estimado</h2>
            <p class="text-2xl font-semibold text-slate-800">${{ number_format($estimatedTotal, 2) }}</p>
            @if($budget)
                <p class="text-xs text-slate-500">Presupuesto objetivo: ${{ number_format($budget, 2) }}</p>
            @endif
        </div>
    </div>

    @if($shoppingList->notes)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-8">
            <h3 class="font-semibold">Notas</h3>
            <p class="text-sm">{{ $shoppingList->notes }}</p>
        </div>
    @endif

    <section class="bg-white border border-slate-100 rounded-xl shadow-sm p-6 mb-8">
        <header class="mb-6">
            <h2 class="text-lg font-semibold text-slate-800">Agregar productos a la lista</h2>
            <p class="text-sm text-slate-500">Busca en el catálogo, captura precios y asigna el pasillo correcto para mantener el recorrido optimizado.</p>
        </header>

        <form method="POST" action="{{ route('shopping-lists.items.store', $shoppingList) }}" class="space-y-6">
            @csrf

            @include('shopping-lists.partials.item-builder', [
                'supermarkets' => $supermarkets,
                'products' => $products,
                'categories' => $categories,
                'productDataset' => $productDataset,
                'supermarketDataset' => $supermarketDataset,
                'sectionDataset' => $sectionDataset,
                'displayMode' => 'modal',
                'defaultSupermarketId' => $shoppingList->supermarket_id,
            ])

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-500">Agregar productos a la lista</button>
            </div>
        </form>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="bg-white border border-slate-100 rounded-xl shadow-sm">
            <header class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Recorrer el supermercado</h2>
                <span class="text-xs text-slate-500">Ordenado por pasillos</span>
            </header>
            <div class="divide-y divide-slate-100">
                @forelse($pendingGroups as $group)
                    @php
                        $sectionLabel = $group['section_number'] !== null
                            ? 'Pasillo ' . $group['section_number']
                            : 'Sin pasillo';

                        if (! empty($group['section_name']) && $group['section_name'] !== 'Sin pasillo') {
                            $sectionLabel .= ' · ' . $group['section_name'];
                        }
                    @endphp
                    <article class="px-6 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $group['supermarket_name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $sectionLabel }}</p>
                            </div>
                            <span class="text-xs text-slate-400">{{ $group['items']->count() }} productos</span>
                        </div>
                        <ul class="space-y-3">
                            @foreach($group['items'] as $item)
                                <li class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $item->product->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->quantity }} {{ $item->quantity_unit ?? $item->product->unit }} · Estimado ${{ number_format($item->estimated_price ?? 0, 2) }}</p>
                                        @if($item->notes)
                                            <p class="text-xs text-slate-400 mt-1">{{ $item->notes }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('shopping-lists.items.status', [$shoppingList, $item]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="in_cart">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-500">Agregar al carrito</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @empty
                    <p class="px-6 py-8 text-sm text-slate-500">¡Felicidades! No tienes pendientes en esta lista.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white border border-slate-100 rounded-xl shadow-sm">
            <header class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">En tu carrito</h2>
                <span class="text-xs text-slate-500">Marca los productos que ya tomaste</span>
            </header>
            <div class="divide-y divide-slate-100">
                @forelse($cartGroups as $group)
                    @php
                        $sectionLabel = $group['section_number'] !== null
                            ? 'Pasillo ' . $group['section_number']
                            : 'Sin pasillo';

                        if (! empty($group['section_name']) && $group['section_name'] !== 'Sin pasillo') {
                            $sectionLabel .= ' · ' . $group['section_name'];
                        }
                    @endphp
                    <article class="px-6 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $group['supermarket_name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $sectionLabel }}</p>
                            </div>
                            <span class="text-xs text-slate-400">{{ $group['items']->count() }} productos</span>
                        </div>
                        <ul class="space-y-3">
                            @foreach($group['items'] as $item)
                                <li class="flex items-start justify-between gap-4 opacity-70">
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $item->product->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->quantity }} {{ $item->quantity_unit ?? $item->product->unit }} · Estimado ${{ number_format($item->estimated_price ?? 0, 2) }}</p>
                                        @if($item->notes)
                                            <p class="text-xs text-slate-400 mt-1">{{ $item->notes }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('shopping-lists.items.status', [$shoppingList, $item]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Volver a la lista</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @empty
                    <p class="px-6 py-8 text-sm text-slate-500">Cuando marques productos se mostrarán aquí.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
