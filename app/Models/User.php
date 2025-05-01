<?php

namespace App\Models;

use App\Events\UserSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'password',
        'prefixname',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $dispatchesEvents = [
        'saved' => UserSaved::class,
    ];
    
    protected static function booted()
    {
        static::saved(function ($user) {
            event(new UserSaved($user));
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    

    public function details()
    {
        return $this->hasMany(Detail::class);
    }
}