<?php

namespace App\Models\Live;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug','title','description','start_time','end_time','stream_url','max_participants','user_id','instractor_id',
    ];

    public function user(){ return $this->belongsTo(User::class);}
    public function resources(){ return $this->hasMany(LiveResource::class);}
    public function participants(){ return $this->hasMany(Participant::class);}
    public function instructor(){ return $this->belongsTo(User::class, 'instractor_id');}
}
