<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

    
class Vehicle extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'year',
        'number',
        'colour',
        'mileage',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['vehicle_name'];

     /**
     * Get the vehicle's name.
     *
     * @return string
     */
    public function getVehicleNameAttribute()
    {
        return "{$this->brand} {$this->model}";
    }

     /**
     * Get the user a vehicle belongs to
     */
    public function user()
    {
        return $this->belongsToMany('App\Models\User');
    }

}
