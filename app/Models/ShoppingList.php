<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingList extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'supermarket_id',
        'name',
        'status',
        'budget',
        'estimated_total',
        'planned_for',
        'completed_at',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'budget' => 'decimal:2',
        'estimated_total' => 'decimal:2',
        'planned_for' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, ShoppingList>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Supermarket, ShoppingList>
     */
    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }

    /**
     * @return HasMany<ShoppingListItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class)->orderBy('position');
    }
}
