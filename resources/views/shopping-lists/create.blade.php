@extends('layouts.app')

@section('title', 'Crear lista de compra')

@section('content')
    <h1 class="text-2xl font-bold text-slate-800 mb-2">Nueva lista inteligente</h1>
    <p class="text-sm text-slate-500 mb-6">Elige un establecimiento, define tu presupuesto y prepara los productos que necesitas.</p>

    <form method="POST" action="{{ route('shopping-lists.store') }}" id="shopping-list-form" class="space-y-8">
        @csrf
        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Detalles de la lista</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Estado de la lista</label>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="status" value="active" class="text-indigo-600" @checked(old('status', 'active') === 'active')>
                            Activa
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="status" value="draft" class="text-indigo-600" @checked(old('status') === 'draft')>
                            Inactiva
                        </label>
                        <p class="text-xs text-slate-500">Puedes activar o pausar la lista en cualquier momento.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="name">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Compra semanal">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="supermarket_id">Establecimiento principal</label>
                    <select id="supermarket_id" name="supermarket_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Selecciona uno</option>
                        @foreach($supermarkets as $market)
                            <option value="{{ $market->id }}" @selected(old('supermarket_id') == $market->id)>{{ $market->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="budget">Presupuesto estimado</label>
                    <input type="number" step="0.01" id="budget" name="budget" value="{{ old('budget') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="1200.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="planned_for">Fecha objetivo</label>
                    <input type="date" id="planned_for" name="planned_for" value="{{ old('planned_for') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-medium text-slate-600 mb-1" for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Añade recordatorios, invitados, etc.">{{ old('notes') }}</textarea>
            </div>
        </section>

        @include('shopping-lists.partials.item-builder', [
            'supermarkets' => $supermarkets,
            'products' => $products,
            'categories' => $categories,
            'productDataset' => $productDataset,
            'supermarketDataset' => $supermarketDataset,
            'sectionDataset' => $sectionDataset,
            'displayMode' => 'inline',
            'defaultSupermarketId' => old('supermarket_id'),
        ])

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-500">Guardar lista y comenzar compra</button>
        </div>
    </form>
@endsection
