<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supermarket extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'brand',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'opening_hours',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'opening_hours' => 'array',
    ];

    /**
     * @return HasMany<SupermarketSection>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(SupermarketSection::class)->orderBy('position');
    }

    /**
     * @return HasMany<InventoryItem>
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * @return HasMany<ShoppingList>
     */
    public function shoppingLists(): HasMany
    {
        return $this->hasMany(ShoppingList::class);
    }
}
