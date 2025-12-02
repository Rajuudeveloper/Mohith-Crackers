<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCollection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agent_id',
        'payment_mode',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ✅ RELATION TO AGENTS MASTER
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery(); // no withoutTrashed
    }
}
