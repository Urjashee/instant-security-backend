<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoleIds extends Model
{
    const ADMIN = 1;
    const USER = 2;
}
