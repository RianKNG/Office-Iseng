<?php

namespace App\Models;

use App\Models\TemplateField;
use Illuminate\Database\Eloquent\Model;

class LetterValue extends Model
{
    protected $table = 'letter_values';
    protected $fillable = ['letter_id', 'field_id', 'nilai'];
    public $timestamps = false; // Sesuai DB, hanya ada created_at default atau tidak dipakai update
    
    public function field() {
        return $this->belongsTo(TemplateField::class, 'field_id');
    }
}