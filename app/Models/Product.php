<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        'name',
        'category',
        'price',
        'discount'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'user_id',
//        'pivot'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products', 'product_id', 'order_id');
    }

    public function images(){
        return $this->hasMany(Image::class);
    }
}
