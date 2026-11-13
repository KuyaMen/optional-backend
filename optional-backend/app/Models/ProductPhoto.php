<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPhoto extends Model
{
    use HasFactory;

    /**
     * 
     *
     * @var string
     */
    protected $table = 'product_photos';

    /**
     * 
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'photo',
    ];

    /**
     * 
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
