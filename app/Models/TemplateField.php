<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $table = 'template_fields';
    
    // Tambahkan ini jika Anda tidak ingin Laravel otomatis mengisi created_at/updated_at
    public $timestamps = false; 

    protected $fillable = [
        'template_id', 
        'nama_field', 
        'tipe_field', 
        'is_required', 
        'opsi_json', 
        'urutan'
    ];

    protected $casts = [
        'opsi_json' => 'array',
        'is_required' => 'boolean'
    ];
}