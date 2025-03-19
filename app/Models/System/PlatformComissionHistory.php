<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class PlatformComissionHistory extends Model {

    protected $fillable = [
        'fees',
        'platform_comission_id',
        'user_id'
    ];
}
