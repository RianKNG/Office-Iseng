<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $fillable = [
        'nama_cabang',
        'kode',
        'tipe', // 'pusat', 'cabang', 'unit'
        'alamat',
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function scopePusat($query) {
        return $query->where('tipe', 'pusat');
    }

    public function scopeCabang($query) {
        return $query->where('tipe', 'cabang');
    }

    public function scopeUnit($query) {
        return $query->where('tipe', 'unit');
    }
}