<?php

namespace App\Models\Live;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VertualClassEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug','price_plan','enrolled_at','end_date','remaining_date','user_id','instractor_id',
    ];

    public function user(){ return $this->belongsTo(User::class);}
    public function instructor(){ return $this->belongsTo(User::class, 'instractor_id');}
}
