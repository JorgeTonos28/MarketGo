<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShoppingListItemStatusController extends Controller
{
    public function __invoke(Request $request, ShoppingList $shoppingList, ShoppingListItem $shoppingListItem): RedirectResponse
    {
        if ($shoppingList->user_id !== $request->user()->id || $shoppingListItem->shopping_list_id !== $shoppingList->id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:pending,in_cart'],
        ]);

        $shoppingListItem->update(['status' => $data['status']]);

        $message = $data['status'] === 'in_cart'
            ? 'Producto marcado como agregado al carrito.'
            : 'Producto devuelto a la lista de pendientes.';

        return back()->with('status', $message);
    }
}
