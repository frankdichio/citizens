<?php

namespace App\Http\Controllers;

use App\Http\Requests\OldFamilyRequest;
use App\Http\Requests\BasicRequest;
use App\Lib\Message;
use App\Models\Citizen;
use App\Models\Family;
use Illuminate\Support\Facades\Log;

class CitizenController extends Controller
{

    const MAX_FAMILIES_TO_BE_IN_CHARGE_FOR_PARENT = 3;
    const MAX_MEMBERS_FOR_PARENT = 6;

    /**
     * promoting a citizen to member in charge of the family
     * 
     * @param Int $citizenId, $familyId
     * 
     * @return Illuminate\Http\Response
     */
    public function promoteToInCharge(BasicRequest $request)
    {
        try {
            Log::info('started process to promote a citizen to in charge');
            $citizen = Citizen::findOrFail($request->citizen_id);
            $family = Family::findOrFail($request->family_id);

            //checking if the citizen is in the family
            $citizenIsInFamily = $citizen->isInFamily($family->id);
            if (!$citizenIsInFamily) {
                Log::error(["error" => Message::NO_CITIZEN_IN_FAMILY]);
                return $this->sendError(Message::NO_CITIZEN_IN_FAMILY);
            }

            //checking if the citizen is not a child in that family
            $isNotAChild = $citizen->isNotAChild($family->id);
            if (!$isNotAChild) {
                Log::error(["error" => Message::NOT_ALLOWED]);
                return $this->sendError(Message::NOT_ALLOWED);
            }

            //checking if the citizen is a parent
            $isParent = $citizen->isParent($family->id);
            if ($isParent) {
                //if he/she is, checking if he's in charge of other families and how many members are in this family
                $familiesWhereInCharge = $citizen->familiesWhereInCharge();
                $membersNumber = $family->membersCount();

                if ($familiesWhereInCharge >= self::MAX_FAMILIES_TO_BE_IN_CHARGE_FOR_PARENT || $membersNumber >= self::MAX_MEMBERS_FOR_PARENT) {
                    Log::error(["error" => Message::NOT_ALLOWED]);
                    return $this->sendError(Message::NOT_ALLOWED);
                }
            }

            //finding the old member in charge
            $oldMemberInCharge = $family->oldMemberInCharge();
            if ($oldMemberInCharge) {
                //removing the old member in charge's title
                $family->citizens()->updateExistingPivot($oldMemberInCharge, [
                    'in_charge' => false,
                ]);
            }

            //giving to the citizen the in charge's title
            $citizen->families()->updateExistingPivot($family->id, [
                'in_charge' => true,
            ]);

            $message = "the member " . $citizen->surname . " is now in charge of the " . $family->name . " family";

            Log::info('citizen promoted');
            return $this->sendResponse($message);
        } catch (\Exception $ex) {
            Log::error(["error" => $ex->getMessage()]);
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * moving a citizen from a family to another
     * 
     * @param Int $citizenId, $familyId, $oldFamilyId
     * 
     * @return Illuminate\Http\Response
     */
    public function movingCitizenFromAFamilyToAnother(OldFamilyRequest $request)
    {
        try {
            Log::info('started process to move a citizen from a family to another');

            $citizen = Citizen::findOrFail($request->citizen_id);
            $family = Family::findOrFail($request->family_id);
            $oldFamily = Family::findOrFail($request->old_family_id);

            //checking if the citizen is in the family
            $citizenIsInFamily = $citizen->isInFamily($oldFamily->id);
            if (!$citizenIsInFamily) {
                Log::error(["error" => Message::NO_CITIZEN_IN_FAMILY]);
                return $this->sendError(Message::NO_CITIZEN_IN_FAMILY);
            }

            //checking if the citizen is not a child in that family
            $citizenCannotLeave = self::cannotLeave($citizen, $family);
            if (!$citizenCannotLeave) {
                Log::error(["error" => Message::NOT_ALLOWED_TO_LEAVE]);
                return $this->sendError(Message::NOT_ALLOWED_TO_LEAVE);
            }

            $role = $citizen->familyRole($oldFamily->id);

            $citizen->families()->detach($oldFamily->id);
            $citizen->families()->attach($family->id, ['role_id' => $role]);

            $message = "the member " . $citizen->surname . " has been moved from the " . $oldFamily->name . " family to the " . $family->name . " family";

            Log::info('citizen moved correctly');
            return $this->sendResponse($message);
        } catch (\Exception $ex) {
            Log::error(["error" => $ex->getMessage()]);
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * adding the citizen to another family
     * 
     * @param Int $citizenId, $familyId
     * 
     * @return Illuminate\Http\Response
     */
    public function addCitizenToAnotherFamily(BasicRequest $request)
    {
        try {
            Log::info('attaching the citizen to a new family');
            $citizen = Citizen::findOrFail($request->citizen_id);
            $family = Family::findOrFail($request->family_id);

            $role = $citizen->familyRole();

            $citizen->families()->attach($family->id, ['role_id' => $role]);

            $message = "the member " . $citizen->surname . " has been to the " . $family->name . " family ";

            Log::info('citizen attached to new family correctly');
            return $this->sendResponse($message);
        } catch (\Exception $ex) {
            Log::error(["error" => $ex->getMessage()]);
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * removing the citizen from a family
     * 
     * @param Int $citizenId, $familyId
     * 
     * @return Illuminate\Http\Response
     */
    public function removeCitizenFromAFamily(BasicRequest $request)
    {
        try {
            Log::info('removing a citizen from a family');

            $citizen = Citizen::findOrFail($request->citizen_id);
            $family = Family::findOrFail($request->family_id);

            //checking if the citizen is in the family
            $citizenIsInFamily = $citizen->isInFamily($family->id);
            if (!$citizenIsInFamily) {
                Log::error(["error" => Message::NO_CITIZEN_IN_FAMILY]);
                return $this->sendError(Message::NO_CITIZEN_IN_FAMILY);
            }

            //checking if the citizen is not a child in that family
            $citizenCannotLeave = self::cannotLeave($citizen, $family);
            if (!$citizenCannotLeave) {
                Log::error(["error" => Message::NOT_ALLOWED_TO_LEAVE]);
                return $this->sendError(Message::NOT_ALLOWED_TO_LEAVE);
            }

            $citizen->families()->detach($family->id);

            $message = "the member " . $citizen->surname . " has been removed from to the " . $family->name . " family ";

            Log::info('citizen removed correctly');
            return $this->sendResponse($message);
        } catch (\Exception $ex) {
            Log::error(["error" => $ex->getMessage()]);
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * checking if citizen cannot leave the family
     * 
     * @param $citizen, $family 
     * 
     * @return boolean
     */
    public function cannotLeave($citizen, $family)
    {
        try {
            //checking if the citizen is not a child in that family
            $childCannotLeave = false;
            $isNotAChild = $citizen->isNotAChild($family->id);
            if (!$isNotAChild) {
                $isUniqueChild = $family->hasOneMember();
                $belongOnlyToOneFamily = $citizen->belongOnlyToOneFamily();

                if ($isUniqueChild && $belongOnlyToOneFamily) {
                    $childCannotLeave = true;
                }
            }

            $memberInCharge = $citizen->isInCharge($family->id);
            if ($memberInCharge || $childCannotLeave) {
                return false;
            }

            return true;
        } catch (\Exception $ex) {
            Log::error(["error" => $ex->getMessage()]);
            return $this->sendError($ex->getMessage());
        }
    }
}
