<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'opening_balance',
        'cr_dr',
        'address',
    ];
}
