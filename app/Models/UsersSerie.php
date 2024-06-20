<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersSerie extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'series_id',
       
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

}