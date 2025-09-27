@props(['categories'])

<div data-modal="create-product" class="hidden fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Agregar nuevo producto</h2>
                <p class="text-xs text-slate-500">Completa la información y quedará disponible en el catálogo.</p>
            </div>
            <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('products.store') }}" class="px-6 py-6 space-y-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-name">Nombre</label>
                    <input id="create-name" name="name" type="text" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Arroz integral premium">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-category">Categoría</label>
                    <select id="create-category" name="product_category_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Selecciona una</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-brand">Marca</label>
                    <input id="create-brand" name="brand" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Marca opcional">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-unit">Unidad</label>
                    <input id="create-unit" name="unit" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="kg, paquete, pieza">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-package">Presentación</label>
                    <input id="create-package" name="package_size" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Bolsa 1 kg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-price">Precio promedio</label>
                    <input id="create-price" name="average_price" type="number" step="0.01" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="120.00">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="create-description">Descripción</label>
                    <textarea id="create-description" name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Notas para reconocer el producto"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" data-close-modal class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-500">Guardar producto</button>
            </div>
        </form>
    </div>
</div>
