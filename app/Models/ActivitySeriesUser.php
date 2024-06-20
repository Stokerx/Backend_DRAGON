<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySeriesUser extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
      'user_id','series_id','chapter_id','is_system_activity','description',
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function function_series()
    {
        return $this->belongsTo(FunctionSerie::class);
    }
    public function type_function()
    {
        return $this->belongsTo(TypeFunction::class);
    }


}
