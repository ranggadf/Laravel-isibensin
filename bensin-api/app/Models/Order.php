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
    'lat',
    'lng',
    'owner_lat',
    'owner_lng',
    'expired_at',
    'hapus_dari_pelanggan',
    'hapus_dari_owner',
    'no_hp',
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