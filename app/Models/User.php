<?php

namespace App\Models;

use App\Models\Disposisi;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

   // File: app/Models/User.php

protected $fillable = [
    'username',
    'password_hash',
    'nama_lengkap',
    'email',
    'jabatan',
    'level',
    'level_urutan',
    'status',
    'signature',
];
    // Override password field karena di DB bernama 'password_hash'
    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function disposisiMasuk() {
        return $this->hasMany(Disposisi::class, 'ke_user_id');
    }
    
    public function disposisiKeluar() {
        return $this->hasMany(Disposisi::class, 'dari_user_id');
    }
}