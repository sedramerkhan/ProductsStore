<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable=[
        'product_id',
        'image'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'product_id',
        'id'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
