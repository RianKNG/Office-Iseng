<?php

namespace App\Models; use Illuminate\Database\Eloquent\Model; class TemplateField extends Model { protected $table = 'template_fields'; protected $fillable = ['template_id', 'nama_field', 'tipe_field', 'is_required', 'opsi_json', 'urutan']; protected $casts = [ 'opsi_json' => 'array', 'is_required' => 'boolean' ]; }