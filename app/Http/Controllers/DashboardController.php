<?php

namespace App\Http\Controllers;

use App\Models\ConsumptionLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $pendingListsCount = $user->shoppingLists()
            ->whereIn('status', ['draft', 'active'])
            ->count();

        $estimatedBudgetAll = $user->shoppingLists()->sum('estimated_total');
        $estimatedBudgetActive = $user->shoppingLists()
            ->where('status', 'active')
            ->sum('estimated_total');

        $lastMonth = Carbon::now()->subMonth();

        $spentLastMonth = $user->consumptionLogs()
            ->where(function ($query) use ($lastMonth): void {
                $query->whereNull('consumed_at')->orWhere('consumed_at', '>=', $lastMonth);
            })
            ->sum('price');

        $consumptionByMarket = ConsumptionLog::query()
            ->with('supermarket')
            ->selectRaw('supermarket_id, sum(price) as total')
            ->where('user_id', $user->id)
            ->where(function ($query) use ($lastMonth): void {
                $query->whereNull('consumed_at')->orWhere('consumed_at', '>=', $lastMonth);
            })
            ->groupBy('supermarket_id')
            ->get()
            ->map(fn ($row) => [
                'name' => optional($row->supermarket)->name ?? 'Sin establecimiento',
                'total' => (float) $row->total,
            ]);

        $activeLists = $user->shoppingLists()
            ->withCount('items')
            ->where('status', 'active')
            ->orderByDesc('planned_for')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $recentLists = $user->shoppingLists()
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentConsumptions = $user->consumptionLogs()
            ->with(['product', 'supermarket'])
            ->orderByDesc('consumed_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'pendingListsCount' => $pendingListsCount,
            'estimatedBudgetAll' => (float) $estimatedBudgetAll,
            'estimatedBudgetActive' => (float) $estimatedBudgetActive,
            'spentLastMonth' => (float) $spentLastMonth,
            'consumptionByMarket' => $consumptionByMarket,
            'activeLists' => $activeLists,
            'recentLists' => $recentLists,
            'recentConsumptions' => $recentConsumptions,
        ]);
    }
}
