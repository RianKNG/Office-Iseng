<?php

namespace App\Models;

use App\Models\TemplateField;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';
    protected $fillable = ['nama_template', 'kode_template', 'jenis', 'deskripsi', 'is_active'];

    public function fields() {
        return $this->hasMany(TemplateField::class, 'template_id')->orderBy('urutan', 'asc');
    }
}