<?php
// ✅ Namespace HARUS persis seperti ini
namespace Database\Seeders;

use App\Models\TemplateField;
use Illuminate\Database\Seeder;

class TemplateFieldSeeder extends Seeder
{
    public function run()
    {
        // Hapus data lama
        TemplateField::query()->delete();

        // Definisi fields
        $fields = [
            ['template_id' => 1, 'nama_field' => 'Nomor Surat Pengirim', 'tipe_field' => 'text', 'is_required' => 1, 'urutan' => 1],
            ['template_id' => 1, 'nama_field' => 'Tanggal Surat Pengirim', 'tipe_field' => 'date', 'is_required' => 1, 'urutan' => 2],
            ['template_id' => 1, 'nama_field' => 'Pengirim', 'tipe_field' => 'text', 'is_required' => 1, 'urutan' => 3],
            ['template_id' => 1, 'nama_field' => 'Isi Ringkas', 'tipe_field' => 'textarea', 'is_required' => 1, 'urutan' => 4],
            ['template_id' => 1, 'nama_field' => 'Disposisi', 'tipe_field' => 'textarea', 'is_required' => 0, 'urutan' => 5],
            ['template_id' => 2, 'nama_field' => 'Kepada', 'tipe_field' => 'select', 'is_required' => 1, 'urutan' => 1],
            ['template_id' => 2, 'nama_field' => 'Isi Surat', 'tipe_field' => 'textarea', 'is_required' => 1, 'urutan' => 2],
            ['template_id' => 2, 'nama_field' => 'Penandatangan', 'tipe_field' => 'text', 'is_required' => 1, 'urutan' => 3],
            ['template_id' => 3, 'nama_field' => 'Dari', 'tipe_field' => 'text', 'is_required' => 1, 'urutan' => 1],
            ['template_id' => 3, 'nama_field' => 'Kepada', 'tipe_field' => 'select', 'is_required' => 1, 'urutan' => 2],
            ['template_id' => 3, 'nama_field' => 'Perihal', 'tipe_field' => 'text', 'is_required' => 1, 'urutan' => 3],
            ['template_id' => 3, 'nama_field' => 'Isi Nota', 'tipe_field' => 'textarea', 'is_required' => 1, 'urutan' => 4],
        ];

        // Insert data
        foreach ($fields as $field) {
            TemplateField::create($field);
        }
    }
}