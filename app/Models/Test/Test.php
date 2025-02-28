<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'question', 'choices', 'answer', 'user_id', 'score', 'level'
    ];

    protected $casts = [
        'choices' => 'array',
        'answer' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($test) {
            $test->slug = Str::uuid();
        });
    }
}
