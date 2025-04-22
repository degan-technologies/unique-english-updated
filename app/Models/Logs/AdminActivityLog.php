<?php

namespace App\Models\Logs;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model {

    protected $fillable = [
        'activity',
        'user_id',
    ];
}
