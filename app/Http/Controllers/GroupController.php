<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Http\Requests\CustomerGroupRequest;
use App\Http\Requests\ContributeAmountRequest;
use App\Http\Requests\WithdrawGroupAmountRequest;
use App\Models\Group;
use App\Traits\ResponseTrait;
use App\Traits\DwollaTrait;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    use ResponseTrait, DwollaTrait;

    public function createGroup(GroupRequest $request)
    {
        $auth_user = auth()->user();
        $group_data = [
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "goal" => $request->input("goal"),
            "user_account_id" => $auth_user->userAccount->id
        ];
        $group = Group::create($group_data);
        return $this->sendSuccessResponse('Group has been added successfully.', $group);
    }

    public function getGroupList()
    {
        $auth_user = auth()->user();
        $group = Group::where('user_account_id', $auth_user->userAccount->id)->get();
        return $this->sendSuccessResponse('Group List.', $group);
    }

    public function deleteGroup($id)
    {
        $group = Group::find($id);
        $group->delete();
        return $this->sendSuccessResponse('Group has been deleted successfully.', $group);
    }

    public function addCustomerGroup(CustomerGroupRequest $request)
    {
        $group = Group::find($request->input("group_id"));
        $group->userAccount()->sync($request->input("customer_id"));
        return $this->sendSuccessResponse('Customer has been added in group successfully.', $group->userAccount);
    }

    public function deleteCustomerGroup(CustomerGroupRequest $request)
    {
        $group = Group::find($request->input("group_id"));
        $group->userAccount()->detach($request->input("customer_id"));
        return $this->sendSuccessResponse('Customer has been deleted from group successfully.', $group->userAccount);
    }

    public function contributeAmount(ContributeAmountRequest $request)
    {
        $group = Group::find($request->input("group_id"));
        $contribute_data = [
            "source" => $request->input("bank_uuid"),
            "destination" => $group->admin->balance_account_uuid,
            "amount" => $request->input("amount")
        ];
        $this->groupContributeAmount($contribute_data);
        $group->amount += $request->input("amount");
        $group->save();
        return $this->sendSuccessResponse('You Successfully Contributed $' . $request->input("amount") . ' to "' . $group->name . '"');
    }

    public function withdrawGroupAmount(WithdrawGroupAmountRequest $request)
    {
        $auth_user = auth()->user();
        $group = Group::where('user_account_id', $auth_user->userAccount->id)->where('id', $request->input("group_id"))->first();
        if (is_null($group)) {
            return $this->sendBadRequestResponse('You are not admin of this group.');
        }
        $group->amount = 0;
        $group->save();
        return $this->sendSuccessResponse('Group fund transfer to admin successfully.', $auth_user->userAccount);
    }
}
