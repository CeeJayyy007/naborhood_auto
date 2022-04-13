<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name'];

    /**
     * Get the vehicles owned by a user.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle');
    }

    /**
     * Get the inventory items created by a user.
     */
    public function inventory()
    {
        return $this->belongsToMany('App\Models\Inventory');
    }


    /**
     * Get the service groups owned by a user.
     */
    public function ServiceGroup()
    {
        return $this->belongsTo('App\Models\ServiceGroup');
    }

    /**
     * Get the service owned by a user.
     */
    public function Service()
    {
        return $this->belongsTo('App\Models\Service');
    }


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

     /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

}
