<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * 
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * 
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'amount',
        'status',
        'order_id',
    ];

    /**
     * 
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}