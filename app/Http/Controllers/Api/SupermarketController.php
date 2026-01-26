<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supermarket;
use Illuminate\Http\Request;

class SupermarketController extends Controller
{
    public function index()
    {
        $supermarkets = Supermarket::with(['sections' => fn ($query) => $query->orderBy('position')->orderBy('name')])
            ->orderBy('name')
            ->get();

        return response()->json($supermarkets);
    }
}
