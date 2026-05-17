<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\TemplateField;

class TemplateFieldKepadaSeeder extends Seeder
{
    public function run()
    {
        $template = Template::where('kode_template', 'SM-UMUM')->first();
        
        if (!$template) {
            $this->command->error('❌ Template SM-UMUM tidak ditemukan!');
            return;
        }

        // Cek apakah field 'Kepada' sudah ada
        $existingField = TemplateField::where('template_id', $template->id)
            ->where('nama_field', 'Kepada')
            ->first();

        if ($existingField) {
            $this->command->warn('⚠️  Field "Kepada" sudah ada di template.');
            return;
        }

        // ✅ GUNAKAN 'tipe_field' (bukan 'jenis_field')
        TemplateField::create([
            'template_id'   => $template->id,
            'nama_field'    => 'Kepada',
            'tipe_field'    => 'text',  // ✅ KOLOM YANG BENAR
            'is_required'   => true,
            'urutan'        => 6,
        ]);

        $this->command->info('✅ Field "Kepada" berhasil ditambahkan.');
    }
}