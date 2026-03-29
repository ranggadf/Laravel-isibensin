<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'produk_id',
        'jenis_bbm',
        'qty',
        'harga'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}