<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
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
    public function user()
    {
        return $this->belongsToMany('App\Models\User');
    }

    
    /**
    * Get the rendered services a request belongs to
    */
    public function request()
    {
        return $this->belongsToMany('App\Models\Request');
    }

    /**
    * Get the inventory items that match the parts
    */
    public function inventory()
    {
        return $this->belongsToMany('App\Models\Inventory');
    }
}
