<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $customer_id
 * @property mixed $guard_id
 * @property mixed $transaction_date
 * @property mixed $amount_to_guard
 * @property mixed $amount_to_app
 */
class Transaction extends Model
{
    use HasFactory;
    protected $with = ["customer", "guard_user"];
    public function customer()
    {
        return $this->belongsTo(User::class, "customer_id");
    }
    public function guard_user()
    {
        return $this->belongsTo(User::class, "guard_id");
    }
}
