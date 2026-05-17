<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $fillable = ['user_id', 'disposisi_id', 'message', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class);
    }
}