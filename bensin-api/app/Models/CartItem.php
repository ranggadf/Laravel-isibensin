<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'warung_id',
        'jenis_bbm',
        'qty',
        'harga'
    ];

    // 🔥 relasi ke cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function warung()
{
    return $this->belongsTo(\App\Models\Warung::class);
}
}
