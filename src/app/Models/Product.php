<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'quantity'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('product_id', 'ASC');
    }

    public function productOrders()
    {
        return $this->hasMany(OrderItem::class)->orderBy('product_id', 'ASC');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
