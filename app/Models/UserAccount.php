<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    use HasFactory;
    protected $table = "user_accounts";
    protected $guarded = [];

    public function userGroups()
    {
        return $this->belongsToMany(Group::class, 'user_group', 'user_account_id', 'group_id',);
    }

    public function groupAdmin()
    {
        return $this->hasMany(Group::class, 'user_account_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
