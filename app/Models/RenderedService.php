<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenderedService extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = ['vehicle_name'];

    /**
    * Get the user a vehicle belongs to
    */
    public function request()
    {
        return $this->belongsToMany('App\Models\Request');
    }

    /**
    * Get the user a service request belongs to
    */
    public function serviceRequest()
    {
        return $this->belongsToMany('App\Models\Request');
    }

     /**
    * Get the services that belong to a service group
    */
    public function service()
    {
        return $this->belongsToMany('App\Models\Service');
    }
    
    /**
    * Get the services that belong to a service group
    */
    public function serviceGroup()
    {
        return $this->belongsToMany('App\Models\ServiceGroup');
    }
}
