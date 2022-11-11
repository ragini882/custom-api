<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    use HasFactory;
    protected $table = "payment_requests";
    protected $guarded = [];

    public function userAccountTo()
    {
        return $this->belongsTo(UserAccount::class, 'to_account_id', 'id');
    }

    public function userAccountFrom()
    {
        return $this->belongsTo(UserAccount::class, 'from_account_id', 'id');
    }
}
