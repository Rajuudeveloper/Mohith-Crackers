<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estimate extends Model
{
    protected $fillable = [
        'customer_id',
        'sub_total',
        'tax',
        'packing_charges',
        'grand_total',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'tax' => 'decimal:2',
        'packing_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }

    // Calculate totals
    public function calculateTotals(): void
    {
        $subTotal = $this->items->sum('total');
        $this->sub_total = $subTotal;
        $this->grand_total = $subTotal + $this->tax + $this->packing_charges;
    }
}
