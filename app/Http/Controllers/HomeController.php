<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\ConsumptionLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShoppingList;
use App\Models\Supermarket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $supermarkets = Supermarket::with('sections')->orderBy('name')->get();

        $selectedSupermarket = $supermarkets->firstWhere('id', (int) $request->query('supermarket'))
            ?? $supermarkets->first();

        $shoppingList = null;

        if ($selectedSupermarket !== null) {
            $shoppingList = ShoppingList::with([
                'items.product.category',
                'items.section',
                'items.inventoryItem',
                'supermarket.sections',
                'user',
            ])
                ->where('supermarket_id', $selectedSupermarket->id)
                ->orderByDesc('planned_for')
                ->orderByDesc('created_at')
                ->first();
        }

        $categoryStats = ProductCategory::withCount('products')
            ->orderBy('name')
            ->get();

        $metrics = [
            'supermarkets' => $supermarkets->count(),
            'products' => Product::count(),
            'categories' => $categoryStats->count(),
            'activeShoppingLists' => ShoppingList::where('status', 'active')->count(),
            'pendingContributions' => Contribution::where('status', 'pending')->count(),
            'consumptionLogs' => ConsumptionLog::count(),
        ];

        $topProductsQuery = Product::query()
            ->with(['category'])
            ->withCount('shoppingListItems')
            ->orderByDesc('shopping_list_items_count')
            ->limit(6);

        if ($selectedSupermarket !== null) {
            $topProductsQuery->with(['inventoryItems' => function ($query) use ($selectedSupermarket): void {
                $query->where('supermarket_id', $selectedSupermarket->id)
                    ->orderByDesc('last_checked_at');
            }]);
        }

        $topProducts = $topProductsQuery->get();

        $recentContributions = Contribution::with(['user'])
            ->latest()
            ->limit(4)
            ->get();

        return view('welcome', [
            'supermarkets' => $supermarkets,
            'selectedSupermarket' => $selectedSupermarket,
            'shoppingList' => $shoppingList,
            'categoryStats' => $categoryStats,
            'topProducts' => $topProducts,
            'recentContributions' => $recentContributions,
            'metrics' => $metrics,
        ]);
    }
}
