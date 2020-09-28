<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Determines if user is a language expert.
     *
     * @return boolean
     */
    public function isLanguageExpert()
    {
        return $this->hasRole('expert');
    }

    /**
     * Determines if user is a master expert.
     *
     * @return boolean
     */
    public function isMasterExpert()
    {
        return $this->hasRole('master');
    }

    /**
     * Determines if user is an administrator.
     *
     * @return boolean
     */
    public function isAdministrator()
    {
        return $this->hasRole('administrator');
    }
}
