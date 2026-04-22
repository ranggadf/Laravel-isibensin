<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{protected $fillable = [
    'user_id',
    'warung_id',
    'total_harga',
    'ongkir',
    'jarak',
    'status',
    'lat',   // ✅ latitude customer
    'lng',
    'expired_at',   // ✅ longitude customer
];

    // relasi ke item
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ✅ TAMBAHKAN INI
    public function warung()
    {
        return $this->belongsTo(Warung::class);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}
}