<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionSerie extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'series_id',
        'type_id',
        'value',
      
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function TypeFunction()
    {
        return $this->belongsTo(TypeFunction::class, 'type_id');
    }
}