<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\SupermarketSection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShoppingListController extends Controller
{
    public function index(Request $request)
    {
        $lists = $request->user()->shoppingLists()
            ->with(['supermarket'])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($lists);
    }

    public function show(Request $request, ShoppingList $shoppingList)
    {
        if ((int) $shoppingList->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shoppingList->load([
            'supermarket.sections' => fn ($query) => $query->orderBy('position')->orderBy('name'),
            'items.product',
            'items.section',
            'items.supermarket',
        ]);

        return response()->json($shoppingList);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supermarket_id' => ['nullable', 'exists:supermarkets,id'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'planned_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'], // Expecting array directly
            'status' => ['required', Rule::in(['active', 'draft'])],
        ]);

        $itemsPayload = $data['items'];

        if (count($itemsPayload) === 0) {
            return response()->json(['message' => 'Agrega al menos un producto a la lista.'], 422);
        }

        $user = $request->user();
        $defaultSupermarketId = Arr::get($data, 'supermarket_id');

        $shoppingList = DB::transaction(function () use ($user, $data, $itemsPayload, $defaultSupermarketId) {
            $shoppingList = ShoppingList::create([
                'user_id' => $user->id,
                'supermarket_id' => $defaultSupermarketId,
                'name' => $data['name'],
                'status' => $data['status'],
                'budget' => Arr::get($data, 'budget'),
                'estimated_total' => 0,
                'planned_for' => Arr::get($data, 'planned_for') ? Carbon::parse($data['planned_for']) : null,
                'notes' => Arr::get($data, 'notes'),
            ]);

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

                ShoppingListItem::create([
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
            }

            $this->refreshListOrderingAndTotals($shoppingList);

            return $shoppingList;
        });

        return response()->json($shoppingList, 201);
    }

    public function update(Request $request, ShoppingList $shoppingList)
    {
        if ((int) $shoppingList->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Handle simple status update for the list itself
        if ($request->has('status')) {
             $data = $request->validate([
                'status' => ['required', Rule::in(['active', 'draft', 'completed', 'archived'])],
            ]);
            $shoppingList->update(['status' => $data['status']]);
            return response()->json($shoppingList);
        }

        return response()->json(['message' => 'Nothing to update'], 400);
    }

    public function toggleItem(Request $request, ShoppingList $shoppingList, ShoppingListItem $item)
    {
        if ((int) $shoppingList->user_id !== (int) $request->user()->id || $item->shopping_list_id !== $shoppingList->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:pending,in_cart'],
        ]);

        $item->update(['status' => $data['status']]);

        return response()->json($item);
    }

    // --- Private Helper Methods (Mirrored from Web Controller) ---

    private function refreshListOrderingAndTotals(ShoppingList $shoppingList): void
    {
        $items = $shoppingList->items()
            ->with(['supermarket', 'section', 'product'])
            ->get();

        $sorted = $items->sortBy(function ($item) use ($shoppingList) {
            $supermarketName = optional($item->supermarket)->name
                ?? optional($shoppingList->supermarket)->name
                ?? 'zzzzzz';
            $sectionPosition = optional($item->section)->position ?? 99999;
            $sectionName = optional($item->section)->name ?? 'zzzzzz';
            $productName = optional($item->product)->name ?? '';

            return sprintf(
                '%s|%05d|%s|%s',
                Str::lower($supermarketName),
                $sectionPosition,
                Str::lower($sectionName),
                Str::lower($productName)
            );
        })->values();

        foreach ($sorted as $index => $item) {
            $position = $index + 1;

            if ($item->position !== $position) {
                $item->update(['position' => $position]);
            }
        }

        $estimatedTotal = $items->sum(fn ($item) => $item->estimated_price ?? 0);

        $shoppingList->update(['estimated_total' => round($estimatedTotal, 2)]);
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

            $sectionName = isset($rawItem['section_name']) ? trim((string) $rawItem['section_name']) : null;

            if ($sectionName === '') {
                $sectionName = null;
            }

            $sectionNumber = isset($rawItem['section_number']) && $rawItem['section_number'] !== ''
                ? (int) $rawItem['section_number']
                : null;

            return [
                'type' => 'existing',
                'product_id' => (int) $rawItem['product_id'],
                'quantity' => isset($rawItem['quantity']) ? (float) $rawItem['quantity'] : 1,
                'quantity_unit' => $rawItem['quantity_unit'] ?? null,
                'estimated_price' => isset($rawItem['estimated_price']) ? (float) $rawItem['estimated_price'] : null,
                'supermarket_id' => isset($rawItem['supermarket_id']) ? (int) $rawItem['supermarket_id'] : $defaultSupermarketId,
                'section_id' => isset($rawItem['section_id']) ? (int) $rawItem['section_id'] : null,
                'section_name' => $sectionName,
                'section_number' => $sectionNumber,
                'notes' => $rawItem['notes'] ?? null,
            ];
        }

        if (empty($rawItem['name']) || empty($rawItem['unit'])) {
            return null;
        }

        $sectionName = isset($rawItem['section_name']) ? trim((string) $rawItem['section_name']) : null;

        if ($sectionName === '') {
            $sectionName = null;
        }

        $sectionNumber = isset($rawItem['section_number']) && $rawItem['section_number'] !== ''
            ? (int) $rawItem['section_number']
            : null;

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
            'section_name' => $sectionName,
            'section_number' => $sectionNumber,
            'category_id' => isset($rawItem['category_id']) ? (int) $rawItem['category_id'] : null,
            'notes' => $rawItem['notes'] ?? null,
        ];
    }

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
        $sectionNumber = $item['section_number'] ?? null;

        if ($sectionId) {
            $section = SupermarketSection::find($sectionId);

            if ($section && (! $supermarketId || $section->supermarket_id === $supermarketId)) {
                $updates = [];

                if ($sectionNumber !== null && $section->position !== $sectionNumber) {
                    $updates['position'] = $sectionNumber;
                }

                if ($sectionName !== '' && $section->name !== $sectionName) {
                    $updates['name'] = $sectionName;
                }

                if ($updates !== []) {
                    $section->update($updates);
                }

                return $section;
            }
        }

        if (! $supermarketId) {
            return null;
        }

        if ($sectionNumber !== null) {
            $existingByNumber = SupermarketSection::where('supermarket_id', $supermarketId)
                ->where('position', $sectionNumber)
                ->first();

            if ($existingByNumber) {
                if ($sectionName !== '' && $existingByNumber->name !== $sectionName) {
                    $existingByNumber->update(['name' => $sectionName]);
                }

                return $existingByNumber;
            }
        }

        if ($sectionName !== '') {
            $existingByName = SupermarketSection::where('supermarket_id', $supermarketId)
                ->where('name', $sectionName)
                ->first();

            if ($existingByName) {
                if ($sectionNumber !== null && $existingByName->position !== $sectionNumber) {
                    $existingByName->update(['position' => $sectionNumber]);
                }

                return $existingByName;
            }
        }

        if ($sectionNumber === null && $sectionName === '') {
            return null;
        }

        $position = $sectionNumber ?? (SupermarketSection::where('supermarket_id', $supermarketId)->max('position') ?? 0) + 1;

        return SupermarketSection::create([
            'supermarket_id' => $supermarketId,
            'name' => $sectionName !== '' ? $sectionName : 'Pasillo '.$position,
            'position' => $position,
            'is_active' => true,
        ]);
    }
}
