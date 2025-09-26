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

        <input type="hidden" name="items" id="items-input" value="[]">

        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-6" data-list-builder
            data-products='@json($products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'brand' => $product->brand,
                'category_id' => $product->product_category_id,
            ]))'
            data-supermarkets='@json($supermarkets->map(fn($market) => [
                'id' => $market->id,
                'name' => $market->name,
            ]))'
            data-sections='@json($supermarkets->flatMap(fn($market) => $market->sections->map(fn($section) => [
                'id' => $section->id,
                'name' => $section->name,
                'supermarket_id' => $section->supermarket_id,
            ])))'>
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Selecciona tus productos</h2>
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-700">Catálogo existente</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Producto del catálogo</label>
                            <select data-existing-product class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Selecciona un producto --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} @if($product->brand)· {{ $product->brand }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad</label>
                            <input type="number" step="0.01" min="0.01" value="1" data-existing-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Unidad</label>
                            <input type="text" data-existing-unit class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="pieza">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Precio estimado (por unidad)</label>
                            <input type="number" step="0.01" min="0" data-existing-price class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="35.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Establecimiento</label>
                            <select data-existing-supermarket class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Igual al de la lista</option>
                                @foreach($supermarkets as $market)
                                    <option value="{{ $market->id }}">{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Pasillo / sección</label>
                            <select data-existing-section class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Selecciona sección</option>
                                @foreach($supermarkets as $market)
                                    <optgroup label="{{ $market->name }}">
                                        @foreach($market->sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">¿No encuentras el pasillo? Escribe uno nuevo</label>
                            <input type="text" data-existing-section-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Almacén">
                        </div>
                    </div>
                    <button type="button" data-action="add-existing" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-500">Agregar a la lista</button>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-700">Agregar producto manual</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Nombre del producto *</label>
                            <input type="text" data-manual-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Pasta artesanal">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Unidad de medida *</label>
                            <input type="text" data-manual-unit class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="paquete">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Marca</label>
                            <input type="text" data-manual-brand class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Marca opcional">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Categoría</label>
                            <select data-manual-category class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Selecciona categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad</label>
                            <input type="number" step="0.01" min="0.01" value="1" data-manual-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Precio estimado (por unidad)</label>
                            <input type="number" step="0.01" min="0" data-manual-price class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="45.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Establecimiento</label>
                            <select data-manual-supermarket class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Igual al de la lista</option>
                                @foreach($supermarkets as $market)
                                    <option value="{{ $market->id }}">{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Pasillo / sección</label>
                            <input type="text" data-manual-section-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Pasillo B">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Notas</label>
                            <textarea rows="2" data-manual-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Detalles adicionales"></textarea>
                        </div>
                    </div>
                    <button type="button" data-action="add-manual" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-500">Crear producto y agregar</button>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Resumen de productos añadidos</h3>
                <div class="overflow-hidden border border-slate-200 rounded-lg">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Producto</th>
                                <th class="px-4 py-2 text-left">Cantidad</th>
                                <th class="px-4 py-2 text-left">Establecimiento</th>
                                <th class="px-4 py-2 text-left">Pasillo</th>
                                <th class="px-4 py-2 text-left">Precio estimado</th>
                                <th class="px-4 py-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody data-items-table class="divide-y divide-slate-200 bg-white">
                            <tr class="empty-placeholder">
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Aún no agregaste productos. Usa el catálogo o crea uno manual.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-500">Guardar lista y comenzar compra</button>
        </div>
    </form>
@endsection
