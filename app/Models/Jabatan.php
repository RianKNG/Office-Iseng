<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = [
        'nama_jabatan',
        'level_key',
        'urutan',
        'scope',
    ];

    public function users() {
        return $this->hasMany(User::class);
    }
}