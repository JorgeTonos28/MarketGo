<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $productsQuery = Product::query()
            ->with(['category', 'inventoryItems.supermarket', 'inventoryItems.section'])
            ->orderBy('name');

        if ($search !== '') {
            $productsQuery->where('name', 'like', '%' . $search . '%');
        }

        $products = $productsQuery->paginate(50);

        return response()->json($products);
    }

    public function store(Request $request)
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
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(6);
        }

        $product = Product::create([
            'product_category_id' => $data['product_category_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'brand' => Arr::get($data, 'brand'),
            'unit' => Arr::get($data, 'unit'),
            'package_size' => Arr::get($data, 'package_size'),
            'average_price' => Arr::get($data, 'average_price'),
            'description' => Arr::get($data, 'description'),
        ]);

        return response()->json($product, 201);
    }
}
