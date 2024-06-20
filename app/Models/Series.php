<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = [
        'img_url',
        'name',
        'day_issue',
        'status',
        'classification',
        
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_series');
    }

    public function chapter()
    {
        return $this->hasMany(Chapter::class);
    }
    public function functionSeries()
    {
        return $this->hasMany(FunctionSerie::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function activitySeriesUsers()
    {
        return $this->hasMany(ActivitySeriesUser::class);
    }

    public function user_serie()
    {
        return $this->hasMany(UserSerie::class);
    }
}