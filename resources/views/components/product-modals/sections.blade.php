@props(['product', 'supermarkets', 'filterLetter' => null])

<div data-modal="sections-product-{{ $product->id }}" class="hidden fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Pasillos por establecimiento</h2>
                <p class="text-xs text-slate-500">Define dónde encontrar <strong>{{ $product->name }}</strong> en cada supermercado.</p>
            </div>
            <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="{{ route('products.sections.update', $product) }}" class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="letter" value="{{ $filterLetter }}">

            <div class="space-y-4">
                @foreach($supermarkets as $supermarket)
                    @php
                        $inventory = $product->inventoryItems->firstWhere('supermarket_id', $supermarket->id);
                        $currentSection = optional($inventory)->section;
                    @endphp
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="font-semibold text-slate-700">{{ $supermarket->name }}</p>
                                <p class="text-xs text-slate-500">{{ $supermarket->sections->count() }} pasillos disponibles</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ $currentSection ? 'Asignado' : 'Sin asignar' }}</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1" for="section-select-{{ $product->id }}-{{ $supermarket->id }}">Pasillo / sección</label>
                                <select id="section-select-{{ $product->id }}-{{ $supermarket->id }}" name="sections[{{ $loop->index }}][section_id]" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Sin asignar</option>
                                    @foreach($supermarket->sections as $section)
                                        <option value="{{ $section->id }}" @selected(optional($currentSection)->id === $section->id)>
                                            Pasillo {{ $section->position }} · {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Descripción actual</label>
                                <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 bg-slate-50">
                                    {{ $currentSection ? 'Pasillo ' . $currentSection->position . ' · ' . $currentSection->name : 'Sin registro' }}
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="sections[{{ $loop->index }}][supermarket_id]" value="{{ $supermarket->id }}">
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" data-close-modal class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-500">Guardar pasillos</button>
            </div>
        </form>
    </div>
</div>
