<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'series_id',
        'function_series_id',
        'is_divided',
        'num_chapter',
        'value',
       
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function functionSerie()
    {
        return $this->belongsTo(FunctionSerie::class, 'function_series_id');
    }

    public function function_series()
    {
        return $this->belongsTo(FunctionSerie::class);
    }
    public function functions()
    {
        return $this->hasManyThrough(FunctionSerie::class, Series::class);
}

}  //