@extends('layouts.app')

@section('title', 'Catálogo de productos')

@section('content')
    @php
        use Illuminate\Support\Str;

        $hasActiveLists = $activeLists->isNotEmpty();
    @endphp

    <div
        data-product-catalog
        data-products='@json($productDataset)'
        data-lists='@json($activeListsDataset)'
        data-sections='@json($sectionDataset)'
    >
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Catálogo de productos</h1>
                <p class="text-sm text-slate-500">Administra la información de tus productos y mantén actualizados sus pasillos por establecimiento.</p>
                @unless($hasActiveLists)
                    <p class="mt-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">Crea o activa una lista para habilitar el envío directo desde aquí.</p>
                @endunless
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-open-modal="create-product" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-500">
                    <span class="text-lg">＋</span>
                    <span>Agregar producto</span>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="mb-6">
            <label class="block text-xs font-semibold text-slate-500 mb-2">Buscar producto por nombre</label>
            <div class="flex items-center gap-2 max-w-xl">
                <input
                    type="text"
                    name="search"
                    value="{{ $searchTerm }}"
                    placeholder="Escribe para filtrar"
                    class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    data-catalog-filter
                >
                <button type="submit" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg border border-slate-200 hover:bg-slate-200">
                    Aplicar
                </button>
            </div>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-catalog-grid>
            @forelse($products as $product)
                <article
                    class="bg-white border border-slate-200 rounded-xl shadow-sm p-5 flex flex-col gap-3"
                    data-product-card
                    data-product-id="{{ $product->id }}"
                    data-product-name="{{ Str::lower($product->name) }}"
                >
                    <header class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800">{{ $product->name }}</h2>
                            <p class="text-xs text-slate-500">{{ $product->brand ?? 'Sin marca' }} · {{ optional($product->category)->name ?? 'Sin categoría' }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">ID #{{ $product->id }}</span>
                    </header>

                    <dl class="grid grid-cols-2 gap-3 text-sm text-slate-600">
                        <div>
                            <dt class="font-medium text-slate-500 uppercase text-xs">Unidad</dt>
                            <dd class="mt-1">{{ $product->unit ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500 uppercase text-xs">Presentación</dt>
                            <dd class="mt-1">{{ $product->package_size ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500 uppercase text-xs">Precio promedio</dt>
                            <dd class="mt-1">{{ $product->average_price ? '$' . number_format($product->average_price, 2) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500 uppercase text-xs">Pasillos registrados</dt>
                            <dd class="mt-1">{{ $product->inventoryItems->whereNotNull('supermarket_section_id')->count() }}</dd>
                        </div>
                    </dl>

                    @if($product->description)
                        <p class="text-sm text-slate-600 border-t border-slate-100 pt-3">{{ $product->description }}</p>
                    @endif

                    <footer class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 mt-auto">
                        <button
                            type="button"
                            data-action="open-add"
                            @class([
                                'px-3 py-2 text-sm rounded-lg transition focus:outline-none focus:ring-2 focus:ring-emerald-200/60',
                                'border border-emerald-200 text-emerald-600 hover:bg-emerald-50' => $hasActiveLists,
                                'border border-slate-200 text-slate-400 cursor-not-allowed opacity-60' => ! $hasActiveLists,
                            ])
                            @unless($hasActiveLists) disabled @endunless
                        >
                            Agregar a lista
                        </button>
                        <button type="button" data-open-modal="edit-product-{{ $product->id }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Editar</button>
                        <button type="button" data-open-modal="sections-product-{{ $product->id }}" class="px-3 py-2 text-sm rounded-lg border border-indigo-200 text-indigo-600 hover:bg-indigo-50">Ver pasillos</button>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('¿Eliminar este producto?');" class="ml-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 text-sm rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50">Eliminar</button>
                        </form>
                    </footer>
                </article>

                <x-product-modals.edit :product="$product" :categories="$categories" :search-term="$searchTerm" />
                <x-product-modals.sections :product="$product" :supermarkets="$supermarkets" :search-term="$searchTerm" />
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white border border-dashed border-slate-300 rounded-xl p-10 text-center">
                    <p class="text-slate-500">No hay productos que coincidan con el filtro seleccionado.</p>
                </div>
            @endforelse
        </div>

        <x-product-modals.create :categories="$categories" />
        <x-product-modals.add-to-list :active-lists="$activeLists" :has-active-lists="$hasActiveLists" />
    </div>
@endsection

@push('modals')
    <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-open-modal]').forEach((button) => {
                    button.addEventListener('click', () => {
                    const modalId = button.dataset.openModal;
                    const modal = document.querySelector(`[data-modal="${modalId}"]`);

                    if (modal) {
                        modal.classList.remove('hidden');
                    }
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('[data-modal]');

                    if (modal) {
                        modal.classList.add('hidden');
                    }
                });

                const filterInput = document.querySelector('[data-catalog-filter]');
                const grid = document.querySelector('[data-catalog-grid]');
                const cards = grid ? Array.from(grid.querySelectorAll('[data-product-card]')) : [];

                const applyFilter = () => {
                    const query = filterInput?.value?.toLowerCase().trim() ?? '';

                    cards.forEach((card) => {
                        const name = card.dataset.productName ?? '';
                        card.classList.toggle('hidden', query !== '' && ! name.includes(query));
                    });
                };

                filterInput?.addEventListener('input', applyFilter);
                applyFilter();
            });
        });
    </script>
@endpush
