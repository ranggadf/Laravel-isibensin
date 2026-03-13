<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersebaranWarung extends Model
{
    protected $table = 'persebaran_warungs';

    protected $fillable = [
        'nama_warung',
        'lat',
        'long',
        'foto_warung'
    ];
}