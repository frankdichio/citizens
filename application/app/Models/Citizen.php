<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Citizen extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'fiscal_code',
    ];

    /**
     * The families that belong to the citizen.
     */
    public function families()
    {
        return $this->belongsToMany(Family::class)->withPivot('role_id');
    }

    /**
     * citizen is not a child of the family
     * 
     * @param int $familyId
     * @return bool
     */
    public function isNotAChild($familyId)
    {
        $isNotAChild = count($this->families()->where('family_id', $familyId)->whereNot('role_id', 1)->get());
        return $isNotAChild;
    }

    /**
     * citizen is in the family
     * 
     * @param int $familyId
     * @return bool
     */
    public function isInFamily($familyId)
    {
        $isInFamily = $this->families()->where('family_id', $familyId)->get();
        return count($isInFamily);
    }

    /**
     * citizen belong only to one family
     * 
     * @return bool
     */
    public function belongOnlyToOneFamily()
    {
        $familiesCount = count($this->families()->get());
        if ($familiesCount !== 1) {
            return false;
        }

        return true;
    }

    /**
     * citizen is in charge of the family
     * 
     * @param int $familyID
     * @return bool
     */
    public function isInCharge($familyId)
    {
        $isInCharge = $this->families()->where('family_id', $familyId)->where('in_charge', 1)->first();
        if (!$isInCharge) {
            return false;
        }

        return true;
    }

    /**
     * role of the citizen in the family
     * 
     * @param int $familyID
     * @return bool
     */
    public function familyRole($familyId = null)
    {
        if ($familyId) {
            $role = $this->families()->where('family_id', $familyId)->first()->pivot->role_id;
        } else {
            $role = $this->families()->first()->pivot->role_id;
        }
        return $role;
    }

    public function isParent($familyId)
    {
        $role = $this->families()->where('family_id', $familyId)->first()->pivot->role_id;
        if ($role != Role::PARENT) {
            return false;
        }

        return true;
    }

    public function isTutor($familyId)
    {
        $role = $this->families()->where('family_id', $familyId)->first()->pivot->role_id;
        if ($role != Role::TUTOR) {
            return false;
        }

        return true;
    }

    public function familiesWhereInCharge()
    {
        $familiesWhereInCharge = $this->families()->where('in_charge', 1)->get();
        return count($familiesWhereInCharge);
    }
}
