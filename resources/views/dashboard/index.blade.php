@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
            <h2 class="text-sm text-slate-500">Listas pendientes</h2>
            <p class="text-3xl font-semibold text-indigo-600">{{ $pendingListsCount }}</p>
            <p class="text-xs text-slate-400 mt-1">Entre borradores y listas activas.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
            <h2 class="text-sm text-slate-500">Presupuesto total estimado</h2>
            <p class="text-3xl font-semibold text-slate-800">${{ number_format($estimatedBudgetAll, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Incluye todas tus listas.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
            <h2 class="text-sm text-slate-500">Presupuesto activo</h2>
            <p class="text-3xl font-semibold text-emerald-600">${{ number_format($estimatedBudgetActive, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Listas que están en progreso.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
            <h2 class="text-sm text-slate-500">Gastado último mes</h2>
            <p class="text-3xl font-semibold text-rose-600">${{ number_format($spentLastMonth, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Según tu historial de consumo.</p>
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white rounded-xl shadow-sm border border-slate-100">
                <header class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Listas activas</h3>
                    <a href="{{ route('shopping-lists.index') }}" class="text-sm text-indigo-600 hover:underline">Ver todas</a>
                </header>
                <ul class="divide-y divide-slate-100">
                    @forelse($activeLists as $list)
                        <li class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-800">{{ $list->name }}</p>
                                <p class="text-xs text-slate-500">{{ optional($list->supermarket)->name ?? 'Sin establecimiento' }} · {{ $list->items_count }} productos · Estimado ${{ number_format($list->estimated_total, 2) }}</p>
                            </div>
                            <a href="{{ route('shopping-lists.show', $list) }}" class="text-sm text-indigo-600 hover:underline">Abrir</a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-slate-500">No tienes listas activas, ¡crea una nueva!</li>
                    @endforelse
                </ul>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-slate-100">
                <header class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Historial reciente</h3>
                    <a href="{{ route('shopping-lists.index') }}" class="text-sm text-indigo-600 hover:underline">Explorar</a>
                </header>
                <ul class="divide-y divide-slate-100">
                    @forelse($recentLists as $list)
                        <li class="px-6 py-4">
                            <p class="font-medium text-slate-800">{{ $list->name }}</p>
                            <p class="text-xs text-slate-500">{{ $list->created_at->diffForHumans() }} · {{ $list->items_count }} productos · Estimado ${{ number_format($list->estimated_total, 2) }}</p>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-slate-500">Aún no has creado listas de compra.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="space-y-6">
            <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Gasto por establecimiento</h3>
                <ul class="space-y-3">
                    @forelse($consumptionByMarket as $market)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">{{ $market['name'] }}</span>
                            <span class="font-semibold text-slate-800">${{ number_format($market['total'], 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Aún no hay datos de consumo registrados.</li>
                    @endforelse
                </ul>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Consumo reciente</h3>
                <ul class="space-y-4 text-sm">
                    @forelse($recentConsumptions as $log)
                        <li>
                            <p class="font-medium text-slate-700">{{ $log->product->name }}</p>
                            <p class="text-xs text-slate-500">{{ optional($log->supermarket)->name ?? 'Sin establecimiento' }} · {{ $log->consumed_at?->format('d/m/Y') ?? 'Fecha estimada' }} · ${{ number_format($log->price ?? 0, 2) }}</p>
                        </li>
                    @empty
                        <li class="text-slate-500">Todavía no registraste consumos.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection
