<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'agent_id',
        'mobile',
        'email',
        'address',
    ];

    // Relationship to Agent
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
