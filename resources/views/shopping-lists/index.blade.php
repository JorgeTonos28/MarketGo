@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Listas de compra')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tus listas de compra</h1>
            <p class="text-sm text-slate-500">Administra tus listas activas, borradores y compras anteriores.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg shadow-sm hover:bg-slate-300">
                <span class="text-lg">＋</span>
                Agregar producto
            </a>
            <a href="{{ route('shopping-lists.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-500">
                <span class="text-lg">＋</span>
                Nueva lista
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Lista</th>
                    <th class="px-6 py-3">Establecimiento</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3">Productos</th>
                    <th class="px-6 py-3">Presupuesto</th>
                    <th class="px-6 py-3">Creada</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($lists as $list)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $list->name }}</p>
                            <p class="text-xs text-slate-500">{{ Str::ucfirst($list->status) }}</p>
                        </td>
                        <td class="px-6 py-4">{{ optional($list->supermarket)->name ?? 'Sin establecimiento' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $list->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($list->status === 'active' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ __($list->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $list->items_count }}</td>
                        <td class="px-6 py-4">${{ number_format($list->estimated_total, 2) }}</td>
                        <td class="px-6 py-4">{{ $list->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('shopping-lists.show', $list) }}" class="text-indigo-600 hover:underline">Abrir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">Todavía no tienes listas de compra. ¡Crea tu primera lista inteligente!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
