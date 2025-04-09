<?php

namespace App\Models\Logo;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'file_path',
    ];
}
