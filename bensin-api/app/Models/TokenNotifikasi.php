<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenNotifikasi extends Model
{
    protected $table = 'token_notifikasi';

    protected $fillable = [
        'user_id',
        'expo_token'
    ];
}