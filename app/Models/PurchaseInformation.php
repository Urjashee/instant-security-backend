<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $transaction_id
 * @property mixed $amount
 * @property mixed $mode
 */
class PurchaseInformation extends Model
{
    protected $table = "purchase_informations";
}
