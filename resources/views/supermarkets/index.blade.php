@extends('layouts.app')

@section('title', 'Establecimientos')

@section('content')
    <div class="grid gap-8 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Establecimientos disponibles</h1>
                    <p class="text-sm text-slate-500">Supermercados, mercados locales y tiendas especiales para tus compras.</p>
                </div>
            </div>
            <div class="space-y-4">
                @forelse($supermarkets as $market)
                    @php
                        $isEditing = old('editing_supermarket_id') == $market->id;
                        $sectionsForEditor = $isEditing
                            ? collect(old('sections', []))
                                ->map(fn ($section) => [
                                    'id' => $section['id'] ?? null,
                                    'number' => $section['number'] ?? null,
                                    'name' => $section['name'] ?? '',
                                ])
                                ->values()
                            : $market->sections->map(fn ($section) => [
                                'id' => $section->id,
                                'number' => $section->position,
                                'name' => $section->name,
                            ])->values();
                    @endphp

                    <article class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">{{ $market->name }}</h2>
                                <p class="text-sm text-slate-500">{{ $market->brand }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $market->city }}, {{ $market->state }} · {{ $market->country }}</p>
                            </div>
                            <div class="text-xs text-slate-500">
                                <span class="font-semibold text-slate-700">{{ $market->sections->count() }}</span> pasillos configurados
                            </div>
                        </div>
                        @if($market->sections->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($market->sections as $section)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-xs text-slate-600">Pasillo {{ $section->position }} · {{ $section->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <details class="mt-4 border-t border-slate-100 pt-4">
                            <summary class="text-sm font-semibold text-indigo-600 cursor-pointer">Editar establecimiento</summary>
                            <div class="mt-3">
                                <form method="POST" action="{{ route('supermarkets.update', $market) }}" class="space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="editing_supermarket_id" value="{{ $market->id }}">

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="name-{{ $market->id }}">Nombre *</label>
                                            <input
                                                type="text"
                                                id="name-{{ $market->id }}"
                                                name="name"
                                                value="{{ $isEditing ? old('name') : $market->name }}"
                                                required
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="brand-{{ $market->id }}">Marca o tipo</label>
                                            <input
                                                type="text"
                                                id="brand-{{ $market->id }}"
                                                name="brand"
                                                value="{{ $isEditing ? old('brand') : $market->brand }}"
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="address-{{ $market->id }}">Dirección</label>
                                            <input
                                                type="text"
                                                id="address-{{ $market->id }}"
                                                name="address_line1"
                                                value="{{ $isEditing ? old('address_line1') : $market->address_line1 }}"
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="city-{{ $market->id }}">Ciudad</label>
                                            <input
                                                type="text"
                                                id="city-{{ $market->id }}"
                                                name="city"
                                                value="{{ $isEditing ? old('city') : $market->city }}"
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="state-{{ $market->id }}">Estado / Provincia</label>
                                            <input
                                                type="text"
                                                id="state-{{ $market->id }}"
                                                name="state"
                                                value="{{ $isEditing ? old('state') : $market->state }}"
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="country-{{ $market->id }}">País</label>
                                            <input
                                                type="text"
                                                id="country-{{ $market->id }}"
                                                name="country"
                                                value="{{ $isEditing ? old('country') : $market->country }}"
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1" for="postal-{{ $market->id }}">Código postal</label>
                                            <input
                                                type="text"
                                                id="postal-{{ $market->id }}"
                                                name="postal_code"
                                                value="{{ $isEditing ? old('postal_code') : $market->postal_code }}"
                                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                        </div>
                                    </div>

                                    <div
                                        data-section-builder
                                        data-sections='@json($sectionsForEditor)'
                                        class="space-y-4"
                                    >
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Número de pasillo</label>
                                                <input type="number" min="0" data-section-number class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="1">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">¿Qué hay en el pasillo?</label>
                                                <input type="text" data-section-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Frutas y verduras">
                                            </div>
                                            <div class="sm:col-span-2 flex justify-end">
                                                <button type="button" data-add-section class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg shadow hover:bg-slate-700">Agregar pasillo</button>
                                            </div>
                                        </div>
                                        @if($isEditing && ($errors->has('sections') || $errors->has('sections.*.number') || $errors->has('sections.*.name')))
                                            <p class="text-xs text-rose-600">Revisa los pasillos, cada uno necesita número y descripción.</p>
                                        @endif
                                        <div data-section-list class="space-y-2"></div>
                                        <div data-section-hidden></div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg shadow hover:bg-emerald-500">Actualizar establecimiento</button>
                                    </div>
                                </form>
                            </div>
                        </details>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Todavía no hay establecimientos configurados.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Agregar establecimiento</h2>
            <p class="text-sm text-slate-500 mb-4">Completa los datos básicos y opcionalmente define los pasillos iniciales.</p>
            <form method="POST" action="{{ route('supermarkets.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="name">Nombre *</label>
                    <input type="text" id="name" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="brand">Marca o tipo</label>
                    <input type="text" id="brand" name="brand" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Supermercado, ferretería, etc.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="address_line1">Dirección</label>
                    <input type="text" id="address_line1" name="address_line1" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Av. Principal 123">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1" for="city">Ciudad</label>
                        <input type="text" id="city" name="city" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1" for="state">Estado / Provincia</label>
                        <input type="text" id="state" name="state" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="country">País</label>
                    <input type="text" id="country" name="country" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="México">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1" for="postal_code">Código postal</label>
                    <input type="text" id="postal_code" name="postal_code" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div data-section-builder data-sections='@json(old('sections', []))' class="space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Número de pasillo</label>
                            <input type="number" min="0" data-section-number class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">¿Qué hay en el pasillo?</label>
                            <input type="text" data-section-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Frutas y verduras">
                        </div>
                        <div class="sm:col-span-2 flex justify-end">
                            <button type="button" data-add-section class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg shadow hover:bg-slate-700">Agregar pasillo</button>
                        </div>
                    </div>
                    @if($errors->has('sections') || $errors->has('sections.*.number') || $errors->has('sections.*.name'))
                        <p class="text-sm text-rose-600">Revisa los pasillos agregados, cada uno necesita número y descripción.</p>
                    @endif
                    <div data-section-list class="space-y-2"></div>
                    <div data-section-hidden></div>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-500">Guardar establecimiento</button>
            </form>
        </section>
    </div>
@endsection
