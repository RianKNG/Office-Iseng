<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    protected $table = 'disposisi';
    
    protected $fillable = [
        'letter_id',
        'parent_id',
        'dari_user_id',
        'ke_user_id',
        'instruksi',
        'catatan_respon',
        'balasan',
        'prioritas',
        'status',
        'deadline',
        'is_locked',
        'urutan_berjenjang', // ✅ Diaktifkan karena ada di database
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_locked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function letter() { return $this->belongsTo(Letter::class, 'letter_id'); }
    public function dari() { return $this->belongsTo(User::class, 'dari_user_id'); }
    public function ke() { return $this->belongsTo(User::class, 'ke_user_id'); }
    public function parent() { return $this->belongsTo(Disposisi::class, 'parent_id'); }
    public function children() { return $this->hasMany(Disposisi::class, 'parent_id'); }
    
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeForUser($query, $userId) { return $query->where('ke_user_id', $userId); }
}