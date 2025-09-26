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
