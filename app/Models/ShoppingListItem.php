<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'shopping_list_id',
        'product_id',
        'inventory_item_id',
        'supermarket_section_id',
        'supermarket_id',
        'quantity',
        'quantity_unit',
        'status',
        'estimated_price',
        'final_price',
        'position',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'position' => 'integer',
    ];

    /**
     * @return BelongsTo<ShoppingList, ShoppingListItem>
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    /**
     * @return BelongsTo<Product, ShoppingListItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<InventoryItem, ShoppingListItem>
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * @return BelongsTo<SupermarketSection, ShoppingListItem>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(SupermarketSection::class, 'supermarket_section_id');
    }

    /**
     * @return BelongsTo<Supermarket, ShoppingListItem>
     */
    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }
}
