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
     * 
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'price',
        'saleprice',
        'stock',
        'status',
        'brand',
        'description',
    ];

    /**
     * 
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * 
     */
    public function productPhotos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class);
    }

    /**
     * 
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 
     */
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'product_id');
    }
}