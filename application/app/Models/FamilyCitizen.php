<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyCitizen extends Pivot
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'citizen_id',
        'citizen_role',
        'in_charge'
    ];
}
