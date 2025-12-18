@props(['product', 'categories', 'searchTerm' => null])

<div data-modal="edit-product-{{ $product->id }}" class="hidden fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-xl w-full overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Editar {{ $product->name }}</h2>
                <p class="text-xs text-slate-500">Los cambios se guardarán directamente en el catálogo.</p>
            </div>
            <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="search" value="{{ $searchTerm }}">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-name-{{ $product->id }}">Nombre</label>
                    <input id="edit-name-{{ $product->id }}" name="name" type="text" value="{{ old('name', $product->name) }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-category-{{ $product->id }}">Categoría</label>
                    <select id="edit-category-{{ $product->id }}" name="product_category_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected($product->product_category_id === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-brand-{{ $product->id }}">Marca</label>
                    <input id="edit-brand-{{ $product->id }}" name="brand" type="text" value="{{ old('brand', $product->brand) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-unit-{{ $product->id }}">Unidad</label>
                    <input id="edit-unit-{{ $product->id }}" name="unit" type="text" value="{{ old('unit', $product->unit) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-package-{{ $product->id }}">Presentación</label>
                    <input id="edit-package-{{ $product->id }}" name="package_size" type="text" value="{{ old('package_size', $product->package_size) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-price-{{ $product->id }}">Precio promedio</label>
                    <input id="edit-price-{{ $product->id }}" name="average_price" type="number" step="0.01" min="0" value="{{ old('average_price', $product->average_price) }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1" for="edit-description-{{ $product->id }}">Descripción</label>
                    <textarea id="edit-description-{{ $product->id }}" name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" data-close-modal class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-500">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
