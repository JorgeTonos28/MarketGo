@props(['activeLists', 'hasActiveLists'])

@php
    $actionTemplate = route('shopping-lists.items.store', ['shopping_list' => '__LIST__']);
@endphp

<div data-modal="product-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Agregar producto a lista activa</h2>
                <p class="text-xs text-slate-500">Elige una lista disponible y confirma los detalles antes de enviarlo.</p>
            </div>
            <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form
            method="POST"
            data-product-add-form
            data-action-template="{{ $actionTemplate }}"
            class="px-6 py-6 space-y-6"
        >
            @csrf
            <input type="hidden" name="items" value="[]" data-add-items>

            <div data-add-body class="space-y-5 {{ $hasActiveLists ? '' : 'hidden' }}">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Selecciona lista activa</label>
                    <select data-add-list class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Selecciona una lista --</option>
                        @foreach($activeLists as $list)
                            <option value="{{ $list->id }}">
                                {{ $list->name }}
                                @if($list->supermarket)
                                    · {{ $list->supermarket->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="border border-slate-200 rounded-xl bg-slate-50 p-4">
                    <h3 class="text-sm font-semibold text-slate-700" data-add-product-name>Selecciona un producto</h3>
                    <p class="text-xs text-slate-500" data-add-product-meta>Las especificaciones del producto aparecerán aquí.</p>
                    <p class="text-sm text-slate-600 mt-3" data-add-product-description>Cuando abras el modal desde un producto se mostrarán sus detalles.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Cantidad</label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0.01" value="1" data-add-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="inline-flex items-center px-3 py-2 text-xs font-medium text-slate-500 bg-slate-100 rounded-lg" data-add-unit>Unidad</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Precio estimado (unidad)</label>
                        <input type="number" step="0.01" min="0" data-add-price class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Pasillo / sección</label>
                    <select data-add-section class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sin pasillo asignado</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500" data-add-section-hint>Se usarán los pasillos del establecimiento principal de la lista seleccionada.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Notas para la lista</label>
                    <textarea rows="2" data-add-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Observaciones o recordatorios"></textarea>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600" data-add-summary>
                    Selecciona una lista activa para preparar el envío del producto.
                </div>
            </div>

            <div data-add-empty class="text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-5 text-center {{ $hasActiveLists ? 'hidden' : '' }}">
                No tienes listas activas disponibles en este momento. Activa una lista existente o crea una nueva desde el módulo de listas de compra.
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" data-close-modal class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button type="submit" data-add-submit class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-500" @unless($hasActiveLists) disabled @endunless>Agregar a la lista</button>
            </div>
        </form>
    </div>
</div>
