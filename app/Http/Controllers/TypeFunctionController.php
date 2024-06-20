<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TypeFunctionController extends Controller
{
    public function series()
    {
        return $this->belongsTo(Series::class, 'function_series')
        ->with('value')
        ->withTimestamps();

    }
}
