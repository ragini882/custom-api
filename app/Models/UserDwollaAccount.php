<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDwollaAccount extends Model
{
    use HasFactory;
    protected $table = "user_dwolla_accounts";
    protected $guarded = [];
}
