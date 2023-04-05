<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'members',
        'in_charge',
    ];

    /**
     * get the members of the family
     */
    public function citizens()
    {
        return $this->belongsToMany(Citizen::class);
    }

    /**
     * old member in charge of the family
     * 
     * @return bool
     */
    public function oldMemberInCharge()
    {
        $oldMemberInCharge = $this->citizens()->where('in_charge', 1)->first();
        if (!$oldMemberInCharge) {
            return false;
        }
        return $oldMemberInCharge->id;
    }

    /**
     * check if the family has only one member
     * 
     * @return bool
     */
    public function hasOneMember()
    {
        $citizensCount = count($this->citizens()->get());
        if ($citizensCount !== 1) {
            return false;
        }

        return true;
    }

    /**
     * count the members of the family
     * 
     * @return bool
     */
    public function membersCount()
    {
        $members = count($this->citizens()->get());
        return $members;
    }
}
