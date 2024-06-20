<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersHistory extends Model
{
    use HasFactory;

    protected $table = 'users_history'; // Añade esta línea

    protected $fillable = [
        'user_id',
        'chapter_id',
        'function_series_id',
        'value',
    ];

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
}
