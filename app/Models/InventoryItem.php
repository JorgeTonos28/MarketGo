<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'supermarket_id',
        'supermarket_section_id',
        'price',
        'currency',
        'availability_status',
        'stock_quantity',
        'last_checked_at',
        'source',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'last_checked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Product, InventoryItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Supermarket, InventoryItem>
     */
    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }

    /**
     * @return BelongsTo<SupermarketSection, InventoryItem>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(SupermarketSection::class, 'supermarket_section_id');
    }
}
