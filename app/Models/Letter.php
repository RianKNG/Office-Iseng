<?php

namespace App\Models;

use App\Models\Disposisi;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $table = 'letters';
    protected $fillable = [
        'template_id', 'nomor_surat', 'tanggal', 'perihal', 'jenis', 
        'status', 'current_level', 'created_by', 'approved_by', 'file_path'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    public function template() {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function values() {
        return $this->hasMany(LetterValue::class, 'letter_id');
    }
    
    public function disposisis() {
        return $this->hasMany(Disposisi::class, 'letter_id');
    }
}