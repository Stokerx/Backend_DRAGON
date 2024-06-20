<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $timestamps = true;
    protected $fillable = [
        'username',
        'password',
        'status',
        'img_perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|string[]
     */
    /*protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }*/



   /* public function chapters()
    {
        return $this->hasManyThrough(Chapter::class,UsersHistory::class,
            'user_id',
            'id',
            'id',
            'chapter_id'
        );
    }*/

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function activitySeriesUser()
    {
        return $this->hasMany(ActivitySeriesUser::class);
    }
    public function series()
    {
        return $this->belongsToMany(Series::class, 'users_series');
    }
}
