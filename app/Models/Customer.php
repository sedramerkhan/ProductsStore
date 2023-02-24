<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens,HasFactory;

    protected $guard='customer';
    protected $fillable =[
        'name',
        'phone_number',
        'password'
    ];

    protected $hidden=[
      'password'
    ];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
