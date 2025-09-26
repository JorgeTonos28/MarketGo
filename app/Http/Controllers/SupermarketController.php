<?php

namespace App\Http\Controllers;

use App\Models\Supermarket;
use App\Models\SupermarketSection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SupermarketController extends Controller
{
    public function index(): View
    {
        $supermarkets = Supermarket::with('sections')->orderBy('name')->get();

        return view('supermarkets.index', [
            'supermarkets' => $supermarkets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'sections' => ['nullable', 'string'],
        ]);

        $name = $data['name'];
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(6);
        $suffix = 1;

        while (Supermarket::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        $supermarket = Supermarket::create([
            'name' => $name,
            'slug' => $slug,
            'brand' => Arr::get($data, 'brand'),
            'address_line1' => Arr::get($data, 'address_line1'),
            'city' => Arr::get($data, 'city'),
            'state' => Arr::get($data, 'state'),
            'country' => Arr::get($data, 'country'),
            'postal_code' => Arr::get($data, 'postal_code'),
            'opening_hours' => null,
        ]);

        $sectionsInput = collect(preg_split('/\r?\n/', (string) Arr::get($data, 'sections', '')))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values();

        foreach ($sectionsInput as $index => $sectionName) {
            SupermarketSection::firstOrCreate([
                'supermarket_id' => $supermarket->id,
                'name' => $sectionName,
            ], [
                'position' => $index + 1,
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('supermarkets.index')
            ->with('status', 'Establecimiento agregado correctamente.');
    }
}
