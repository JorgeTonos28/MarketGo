@php
    $displayMode = $displayMode ?? 'inline';
    $isModalMode = $displayMode === 'modal';
@endphp

<input type="hidden" name="items" id="items-input" value="{{ old('items', '[]') }}">

<section
    class="bg-white rounded-xl shadow-sm border border-slate-100 p-6"
    data-list-builder
    data-display-mode="{{ $displayMode }}"
    data-products='@json($productDataset ?? [])'
    data-supermarkets='@json($supermarketDataset ?? [])'
    data-sections='@json($sectionDataset ?? [])'
    data-default-supermarket="{{ $defaultSupermarketId ?? '' }}"
    data-supermarket-field="#supermarket_id"
>
    <header class="flex flex-col gap-2 mb-6 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Preparar productos</h2>
            <p class="text-sm text-slate-500">Selecciona productos del catálogo o agrega nuevos manualmente. Todos aparecerán en la cola de preparación.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" data-action="open-existing" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-200 text-indigo-600 hover:bg-indigo-50">
                <span class="text-lg">＋</span>
                Catálogo
            </button>
            <button type="button" data-action="open-manual" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">
                <span class="text-lg">＋</span>
                Manual
            </button>
        </div>
    </header>

    @error('items')
        <p class="mb-4 text-sm text-rose-600">{{ $message }}</p>
    @enderror

    <div class="space-y-4" data-items-queue>
        <div data-empty-placeholder class="border border-dashed border-slate-300 rounded-lg p-6 text-center text-sm text-slate-500">
            Aún no agregaste productos. Usa el catálogo o crea uno manual para comenzar tu cola.
        </div>
    </div>

    @if(! $isModalMode)
        <div class="grid gap-6 mt-8 lg:grid-cols-2" data-inline-forms>
            <div class="border border-slate-200 rounded-xl p-5" data-existing-block>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Catálogo existente</h3>
                <div class="space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Filtrar por inicial</label>
                            <select data-existing-letter class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                @foreach(range('A', 'Z') as $letterOption)
                                    <option value="{{ $letterOption }}">{{ $letterOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Buscar producto</label>
                            <input type="text" data-existing-filter placeholder="Escribe para filtrar" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Selecciona producto</label>
                        <select data-existing-product class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Selecciona --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} @if($product->brand)· {{ $product->brand }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad</label>
                            <input type="number" step="0.01" min="0.01" value="1" data-existing-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Notas para la lista</label>
                            <input type="text" data-existing-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Observaciones opcionales">
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-500">Los productos se agregan automáticamente a la cola al seleccionarlos.</p>
            </div>

            <div class="border border-slate-200 rounded-xl p-5" data-manual-block>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Agregar producto manual</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Nombre *</label>
                        <input type="text" data-manual-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Producto especial">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Unidad *</label>
                        <input type="text" data-manual-unit class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="pieza, paquete">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Marca</label>
                        <input type="text" data-manual-brand class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad</label>
                        <input type="number" step="0.01" min="0.01" value="1" data-manual-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Precio estimado (unidad)</label>
                        <input type="number" step="0.01" min="0" data-manual-price class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="45.00">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Establecimiento</label>
                        <select data-manual-supermarket class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Igual al de la lista</option>
                            @foreach($supermarkets as $market)
                                <option value="{{ $market->id }}">{{ $market->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Pasillo / sección</label>
                        <select data-manual-section class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Selecciona un pasillo</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Notas para la lista</label>
                        <textarea rows="2" data-manual-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Detalles adicionales"></textarea>
                    </div>
                </div>
                <button type="button" data-action="add-manual" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-500">Crear y agregar a la cola</button>
            </div>
        </div>
    @endif

    <template data-item-template>
        <article class="border border-slate-200 rounded-xl p-4 bg-slate-50 flex flex-col gap-2">
            <header class="flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-slate-800" data-item-name></h3>
                    <p class="text-xs text-slate-500" data-item-meta></p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-slate-200 text-slate-600" data-item-type></span>
            </header>
            <p class="text-sm text-slate-600" data-item-description></p>
            <div class="text-xs text-slate-500" data-item-notes></div>
            <div class="flex items-center gap-3 pt-2 border-t border-slate-200 mt-2">
                <button type="button" data-action="edit-item" class="text-sm text-indigo-600 hover:underline">Editar</button>
                <button type="button" data-action="remove-item" class="text-sm text-rose-600 hover:underline">Eliminar</button>
            </div>
        </article>
    </template>

    <div data-modal="builder-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Editar producto en cola</h3>
                    <p class="text-xs text-slate-500">Ajusta la información antes de guardar en la lista.</p>
                </div>
                <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <div class="px-6 py-6 space-y-5" data-edit-form-container></div>
        </div>
    </div>

    @if($isModalMode)
        <div data-modal="builder-existing" class="hidden fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Agregar desde catálogo</h3>
                        <p class="text-xs text-slate-500">Selecciona un producto y se añadirá a la cola.</p>
                    </div>
                    <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="px-6 py-6 space-y-4" data-existing-block>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Filtrar por inicial</label>
                            <select data-existing-letter class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                @foreach(range('A', 'Z') as $letterOption)
                                    <option value="{{ $letterOption }}">{{ $letterOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Buscar producto</label>
                            <input type="text" data-existing-filter placeholder="Escribe para filtrar" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Selecciona producto</label>
                        <select data-existing-product class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Selecciona --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} @if($product->brand)· {{ $product->brand }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad</label>
                            <input type="number" step="0.01" min="0.01" value="1" data-existing-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Notas para la lista</label>
                            <input type="text" data-existing-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Observaciones opcionales">
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Al seleccionar un producto se agregará automáticamente a la cola.</p>
                </div>
            </div>
        </div>

        <div data-modal="builder-manual" class="hidden fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Nuevo producto manual</h3>
                        <p class="text-xs text-slate-500">Se creará en la base de datos y se agregará a la cola.</p>
                    </div>
                    <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="px-6 py-6 space-y-4" data-manual-form-wrapper data-manual-block>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Nombre *</label>
                            <input type="text" data-manual-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Producto especial">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Unidad *</label>
                            <input type="text" data-manual-unit class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="pieza, paquete">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Marca</label>
                            <input type="text" data-manual-brand class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Opcional">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad</label>
                            <input type="number" step="0.01" min="0.01" value="1" data-manual-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Precio estimado (unidad)</label>
                            <input type="number" step="0.01" min="0" data-manual-price class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="45.00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Establecimiento</label>
                            <select data-manual-supermarket class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Igual al de la lista</option>
                                @foreach($supermarkets as $market)
                                    <option value="{{ $market->id }}">{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Pasillo / sección</label>
                            <select data-manual-section class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Selecciona un pasillo</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Notas para la lista</label>
                            <textarea rows="2" data-manual-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Detalles adicionales"></textarea>
                        </div>
                    </div>
                    <button type="button" data-action="add-manual" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-500">Crear y agregar a la cola</button>
                </div>
            </div>
        </div>
    @endif
</section>
