<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\Supermarket;
use App\Models\SupermarketSection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;

class ShoppingListController extends Controller
{
    public function index(Request $request): View
    {
        $lists = $request->user()->shoppingLists()
            ->with(['supermarket'])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        return view('shopping-lists.index', [
            'lists' => $lists,
        ]);
    }

    public function create(Request $request): View
    {
        $supermarkets = Supermarket::with('sections')->orderBy('name')->get();
        $products = Product::with('category')->orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();

        return view('shopping-lists.create', [
            'supermarkets' => $supermarkets,
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supermarket_id' => ['nullable', 'exists:supermarkets,id'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'planned_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'string'],
        ]);

        try {
            $itemsPayload = json_decode($data['items'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'No pudimos interpretar los productos seleccionados.']);
        }

        if (! is_array($itemsPayload) || count($itemsPayload) === 0) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Agrega al menos un producto a la lista.']);
        }

        $user = $request->user();
        $defaultSupermarketId = Arr::get($data, 'supermarket_id');

        $shoppingList = DB::transaction(function () use ($user, $data, $itemsPayload, $defaultSupermarketId) {
            $shoppingList = ShoppingList::create([
                'user_id' => $user->id,
                'supermarket_id' => $defaultSupermarketId,
                'name' => $data['name'],
                'status' => 'active',
                'budget' => Arr::get($data, 'budget'),
                'estimated_total' => 0,
                'planned_for' => Arr::get($data, 'planned_for') ? Carbon::parse($data['planned_for']) : null,
                'notes' => Arr::get($data, 'notes'),
            ]);

            $totalEstimate = 0;
            $createdItems = [];

            foreach ($itemsPayload as $rawItem) {
                $item = $this->normalizeItemPayload($rawItem, $defaultSupermarketId);

                if ($item === null) {
                    continue;
                }

                [$product, $section] = $this->resolveProductAndSection($item);

                $itemSupermarketId = $item['supermarket_id'] ?? $defaultSupermarketId;
                $quantity = $item['quantity'] ?? 1;
                $estimatedUnitPrice = $item['estimated_price'];
                $estimatedPrice = $estimatedUnitPrice !== null
                    ? round($estimatedUnitPrice * $quantity, 2)
                    : null;

                $listItem = ShoppingListItem::create([
                    'shopping_list_id' => $shoppingList->id,
                    'product_id' => $product->id,
                    'supermarket_id' => $itemSupermarketId,
                    'supermarket_section_id' => $section?->id,
                    'quantity' => $quantity,
                    'quantity_unit' => $item['quantity_unit'] ?? $product->unit,
                    'status' => 'pending',
                    'estimated_price' => $estimatedPrice,
                    'notes' => $item['notes'] ?? null,
                    'position' => 0,
                ]);

                if ($estimatedPrice !== null) {
                    $totalEstimate += $estimatedPrice;
                }

                $createdItems[] = [
                    'model' => $listItem,
                    'supermarket_name' => optional($listItem->supermarket)->name ?? '',
                    'section_name' => $section?->name ?? $item['section_name'] ?? '',
                    'product_name' => $product->name,
                ];
            }

            $orderedItems = collect($createdItems)
                ->sortBy([['supermarket_name', 'asc'], ['section_name', 'asc'], ['product_name', 'asc']])
                ->values();

            foreach ($orderedItems as $index => $item) {
                $item['model']->update(['position' => $index + 1]);
            }

            $shoppingList->update(['estimated_total' => round($totalEstimate, 2)]);

            return $shoppingList;
        });

        return redirect()
            ->route('shopping-lists.show', $shoppingList)
            ->with('status', 'Lista creada y lista para ir de compras.');
    }

    public function show(Request $request, ShoppingList $shoppingList): View
    {
        if ($shoppingList->user_id !== $request->user()->id) {
            abort(403);
        }

        $shoppingList->load([
            'supermarket.sections' => fn ($query) => $query->orderBy('name'),
            'items.product',
            'items.section',
            'items.supermarket',
        ]);

        $pendingItems = $shoppingList->items->where('status', 'pending');
        $inCartItems = $shoppingList->items->where('status', 'in_cart');

        $groupedPending = $pendingItems
            ->groupBy(fn ($item) => ($item->supermarket?->name ?? 'Sin establecimiento') . '||' . ($item->section?->name ?? 'Sin pasillo'));

        $groupedCart = $inCartItems
            ->groupBy(fn ($item) => ($item->supermarket?->name ?? 'Sin establecimiento') . '||' . ($item->section?->name ?? 'Sin pasillo'));

        return view('shopping-lists.show', [
            'shoppingList' => $shoppingList,
            'groupedPending' => $groupedPending,
            'groupedCart' => $groupedCart,
        ]);
    }

    private function normalizeItemPayload(array $rawItem, ?int $defaultSupermarketId): ?array
    {
        $type = $rawItem['type'] ?? null;

        if (! in_array($type, ['existing', 'manual'], true)) {
            return null;
        }

        if ($type === 'existing') {
            if (empty($rawItem['product_id'])) {
                return null;
            }

            return [
                'type' => 'existing',
                'product_id' => (int) $rawItem['product_id'],
                'quantity' => isset($rawItem['quantity']) ? (float) $rawItem['quantity'] : 1,
                'quantity_unit' => $rawItem['quantity_unit'] ?? null,
                'estimated_price' => isset($rawItem['estimated_price']) ? (float) $rawItem['estimated_price'] : null,
                'supermarket_id' => isset($rawItem['supermarket_id']) ? (int) $rawItem['supermarket_id'] : $defaultSupermarketId,
                'section_id' => isset($rawItem['section_id']) ? (int) $rawItem['section_id'] : null,
                'section_name' => $rawItem['section_name'] ?? null,
                'notes' => $rawItem['notes'] ?? null,
            ];
        }

        if (empty($rawItem['name']) || empty($rawItem['unit'])) {
            return null;
        }

        return [
            'type' => 'manual',
            'name' => trim($rawItem['name']),
            'brand' => $rawItem['brand'] ?? null,
            'unit' => trim($rawItem['unit']),
            'package_size' => $rawItem['package_size'] ?? null,
            'quantity' => isset($rawItem['quantity']) ? (float) $rawItem['quantity'] : 1,
            'quantity_unit' => $rawItem['quantity_unit'] ?? null,
            'estimated_price' => isset($rawItem['estimated_price']) ? (float) $rawItem['estimated_price'] : null,
            'supermarket_id' => isset($rawItem['supermarket_id']) ? (int) $rawItem['supermarket_id'] : $defaultSupermarketId,
            'section_id' => isset($rawItem['section_id']) ? (int) $rawItem['section_id'] : null,
            'section_name' => $rawItem['section_name'] ?? null,
            'category_id' => isset($rawItem['category_id']) ? (int) $rawItem['category_id'] : null,
            'notes' => $rawItem['notes'] ?? null,
        ];
    }

    /**
     * @return array{0: \App\Models\Product, 1: (\App\Models\SupermarketSection|null)}
     */
    private function resolveProductAndSection(array $item): array
    {
        if ($item['type'] === 'existing') {
            $product = Product::findOrFail($item['product_id']);
            $section = $this->resolveSection($item);

            return [$product, $section];
        }

        $category = $item['category_id']
            ? ProductCategory::find($item['category_id'])
            : ProductCategory::firstOrCreate(
                ['slug' => 'otros'],
                ['name' => 'Otros', 'icon' => 'ph:basket', 'description' => 'Productos sin categoría específica.']
            );

        $name = $item['name'];
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'brand' => $item['brand'] ?? null,
            'package_size' => $item['package_size'] ?? null,
            'unit' => $item['unit'],
            'average_price' => $item['estimated_price'] ?? null,
        ]);

        $section = $this->resolveSection($item);

        return [$product, $section];
    }

    private function resolveSection(array $item): ?SupermarketSection
    {
        $supermarketId = $item['supermarket_id'] ?? null;
        $sectionId = $item['section_id'] ?? null;
        $sectionName = isset($item['section_name']) ? trim($item['section_name']) : '';

        if ($sectionId) {
            $section = SupermarketSection::find($sectionId);

            if ($section && (! $supermarketId || $section->supermarket_id === $supermarketId)) {
                return $section;
            }
        }

        if ($supermarketId && $sectionName !== '') {
            $existing = SupermarketSection::where('supermarket_id', $supermarketId)
                ->where('name', $sectionName)
                ->first();

            if ($existing) {
                return $existing;
            }

            $position = (SupermarketSection::where('supermarket_id', $supermarketId)->max('position') ?? 0) + 1;

            return SupermarketSection::create([
                'supermarket_id' => $supermarketId,
                'name' => $sectionName,
                'position' => $position,
                'is_active' => true,
            ]);
        }

        return null;
    }
}
