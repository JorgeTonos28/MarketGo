<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumptionLog extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'supermarket_id',
        'quantity',
        'quantity_unit',
        'price',
        'consumed_at',
        'next_restock_at',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'consumed_at' => 'datetime',
        'next_restock_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, ConsumptionLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, ConsumptionLog>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Supermarket, ConsumptionLog>
     */
    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }
}
