<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "group";
    protected $guarded = [];

    public function userAccount()
    {
        return $this->belongsToMany(UserAccount::class, 'user_group', 'group_id', 'user_account_id');
    }

    public function admin()
    {
        return $this->belongsTo(UserAccount::class, 'user_account_id', 'id');
    }
}
