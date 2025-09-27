<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShoppingList;
use App\Models\Supermarket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $letter = $request->string('letter')->toString();

        $productsQuery = Product::query()
            ->with(['category', 'inventoryItems.supermarket', 'inventoryItems.section'])
            ->orderBy('name');

        if ($letter) {
            $productsQuery->where('name', 'like', $letter . '%');
        }

        $products = $productsQuery->get();

        $categories = ProductCategory::orderBy('name')->get();
        $supermarkets = Supermarket::with(['sections' => fn ($query) => $query->orderBy('position')->orderBy('name')])
            ->orderBy('name')
            ->get();

        $productDataset = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'unit' => $product->unit,
                'package_size' => $product->package_size,
                'average_price' => $product->average_price,
                'description' => $product->description,
                'category_id' => $product->product_category_id,
                'product_category_id' => $product->product_category_id,
                'inventory' => $product->inventoryItems->map(function ($item) {
                    return [
                        'supermarket_id' => $item->supermarket_id,
                        'supermarket_name' => optional($item->supermarket)->name,
                        'section_id' => $item->supermarket_section_id,
                        'section_name' => optional($item->section)->name,
                        'section_position' => optional($item->section)->position,
                    ];
                })->values(),
            ];
        })->values();

        $sectionDataset = $supermarkets->flatMap(function ($market) {
            return $market->sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'position' => $section->position,
                    'supermarket_id' => $section->supermarket_id,
                ];
            });
        })->values();

        $activeLists = $request->user()->shoppingLists()
            ->with('supermarket')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $activeListsDataset = $activeLists->map(function (ShoppingList $list) {
            return [
                'id' => $list->id,
                'name' => $list->name,
                'supermarket_id' => $list->supermarket_id,
                'supermarket_name' => optional($list->supermarket)->name,
            ];
        })->values();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'supermarkets' => $supermarkets,
            'filterLetter' => $letter,
            'productDataset' => $productDataset,
            'sectionDataset' => $sectionDataset,
            'activeLists' => $activeLists,
            'activeListsDataset' => $activeListsDataset,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'package_size' => ['nullable', 'string', 'max:255'],
            'average_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($data['name']);

        $existing = Product::where('slug', $slug)->exists();

        if ($existing) {
            $slug .= '-' . Str::random(6);
        }

        Product::create([
            'product_category_id' => $data['product_category_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'brand' => Arr::get($data, 'brand'),
            'unit' => Arr::get($data, 'unit'),
            'package_size' => Arr::get($data, 'package_size'),
            'average_price' => Arr::get($data, 'average_price'),
            'description' => Arr::get($data, 'description'),
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'package_size' => ['nullable', 'string', 'max:255'],
            'average_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $product->fill($data);

        if ($product->isDirty('name')) {
            $product->slug = Str::slug($data['name']);
        }

        $product->save();

        if ($request->expectsJson()) {
            $product->refresh();

            return response()->json([
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'unit' => $product->unit,
                    'package_size' => $product->package_size,
                    'average_price' => $product->average_price,
                    'description' => $product->description,
                    'product_category_id' => $product->product_category_id,
                ],
            ]);
        }

        return redirect()
            ->route('products.index', ['letter' => $request->input('letter')])
            ->with('status', 'Producto actualizado.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index', ['letter' => $request->input('letter')])
            ->with('status', 'Producto eliminado.');
    }

    public function updateSections(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'sections' => ['array'],
            'sections.*.supermarket_id' => ['required', 'exists:supermarkets,id'],
            'sections.*.section_id' => ['nullable', 'exists:supermarket_sections,id'],
        ]);

        $sections = collect($data['sections'] ?? [])
            ->map(fn ($item) => [
                'supermarket_id' => (int) $item['supermarket_id'],
                'section_id' => $item['section_id'] ? (int) $item['section_id'] : null,
            ]);

        DB::transaction(function () use ($product, $sections) {
            $inventoryItems = $product->inventoryItems()->get()->keyBy('supermarket_id');

            foreach ($sections as $entry) {
                $supermarketId = $entry['supermarket_id'];
                $sectionId = $entry['section_id'];
                $inventoryItem = $inventoryItems->get($supermarketId);

                if ($inventoryItem) {
                    $inventoryItem->update([
                        'supermarket_section_id' => $sectionId,
                    ]);
                } else {
                    InventoryItem::create([
                        'product_id' => $product->id,
                        'supermarket_id' => $supermarketId,
                        'supermarket_section_id' => $sectionId,
                    ]);
                }
            }
        });

        return redirect()
            ->route('products.index', ['letter' => $request->input('letter')])
            ->with('status', 'Pasillos actualizados.');
    }
}
