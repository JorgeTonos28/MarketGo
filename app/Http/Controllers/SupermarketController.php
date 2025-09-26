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
        $supermarkets = Supermarket::with(['sections' => fn ($query) => $query->orderBy('position')->orderBy('name')])
            ->orderBy('name')
            ->get();

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
            'sections' => ['nullable', 'array'],
            'sections.*.number' => ['required', 'integer', 'min:0', 'max:65535'],
            'sections.*.name' => ['required', 'string', 'max:255'],
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

        $sectionsInput = collect(Arr::get($data, 'sections', []))
            ->map(fn ($section) => [
                'number' => (int) Arr::get($section, 'number', 0),
                'name' => trim((string) Arr::get($section, 'name', '')),
            ])
            ->filter(fn ($section) => $section['name'] !== '')
            ->unique(fn ($section) => $section['number'].'|'.$section['name'])
            ->sortBy('number')
            ->values();

        foreach ($sectionsInput as $sectionData) {
            SupermarketSection::updateOrCreate(
                [
                    'supermarket_id' => $supermarket->id,
                    'name' => $sectionData['name'],
                ],
                [
                    'position' => $sectionData['number'],
                    'is_active' => true,
                ],
            );
        }

        return redirect()
            ->route('supermarkets.index')
            ->with('status', 'Establecimiento agregado correctamente.');
    }
}
