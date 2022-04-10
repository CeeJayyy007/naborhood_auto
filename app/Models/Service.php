<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Service extends Model
{
    use HasFactory, SoftDeletes;
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
    public function user()
    {
        return $this->belongsToMany('App\Models\User');
    }

     /**
    * Get the services that belong to a service group
    */
    public function serviceGroup()
    {
        return $this->belongsToMany('App\Models\ServiceGroup');
    }

     /**
    * Get the rendered services a request belongs to
    */
    public function renderedService()
    {
        return $this->belongsToMany('App\Models\RenderedService');
    }

}
