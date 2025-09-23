<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_category_id',
        'name',
        'slug',
        'brand',
        'barcode',
        'package_size',
        'unit',
        'average_price',
        'image_url',
        'description',
        'nutrition_facts',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'average_price' => 'decimal:2',
        'nutrition_facts' => 'array',
    ];

    /**
     * @return BelongsTo<ProductCategory, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return HasMany<InventoryItem>
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * @return HasMany<ShoppingListItem>
     */
    public function shoppingListItems(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    /**
     * @return HasMany<ConsumptionLog>
     */
    public function consumptionLogs(): HasMany
    {
        return $this->hasMany(ConsumptionLog::class);
    }
}
