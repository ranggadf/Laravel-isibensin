<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warung extends Model
{
    protected $fillable = [
        'user_id',
        'nama_warung',
        'alamat',
        'latitude',
        'longitude',
        'stok_pertalite',
        'stok_pertamax',
        'harga_pertalite',
        'harga_pertamax',
        'foto'
    ];
}