<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    use HasFactory;

    /**
     * 
     *
     * @var string
     */
    protected $table = 'order_product';

    /**
     * 
     * 
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 
     * 
     * 
     * 
     *
     * @var string
     */
    protected $primaryKey = 'order_id';

    /**
     * 
     *
     * @var array<int, string>
     */
    protected array $compositeKey = ['order_id', 'product_id'];

    /**
     * 
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'total',
    ];

    /**
     * 
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * 
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * 
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    protected function setKeysForSelectQuery($query)
    {
        return $this->addCompositeKeyConstraints($query);
    }

    /**
     * 
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    protected function setKeysForSaveQuery($query)
    {
        return $this->addCompositeKeyConstraints($query);
    }

    /**
     * 
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    private function addCompositeKeyConstraints($query)
    {
        foreach ($this->compositeKey as $column) {
            $query->where($column, '=', $this->getAttribute($column));
        }

        return $query;
    }
}