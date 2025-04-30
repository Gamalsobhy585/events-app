<?php

namespace App\Models;

use App\Events\UserSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;


    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'prefixname',
        'photo',
        'email',
    ];


    protected static function booted()
    {
        static::saved(function ($user) {
            event(new UserSaved($user));
        });
    }


    public function details()
    {
        return $this->hasMany(Detail::class);
    }
}