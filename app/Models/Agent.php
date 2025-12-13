<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'opening_balance',
        'cr_dr',
        'address',
        'gst_no',
    ];
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(AgentCollection::class);
    }
}
