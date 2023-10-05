<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoleType
{
    const SUPER_ADMIN = "super_admin";
    const CUSTOMER = "customer";
    const USER = "user";
}
