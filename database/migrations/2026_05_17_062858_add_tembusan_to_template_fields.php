<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTembusanToTemplateFields extends Migration
{
    public function up()
    {
        // Cek apakah kolom created_at & updated_at ada
        $hasTimestamps = Schema::hasColumn('template_fields', 'created_at') && 
                         Schema::hasColumn('template_fields', 'updated_at');
        
        // Ambil semua ID template yang aktif
        $templateIds = DB::table('templates')->pluck('id');
        
        foreach ($templateIds as $templateId) {
            // Cek apakah field 'tembusan' sudah ada untuk template ini
            $exists = DB::table('template_fields')
                ->where('template_id', $templateId)
                ->where('nama_field', 'tembusan')
                ->exists();
            
            if (!$exists) {
                $data = [
                    'template_id' => $templateId,
                    'nama_field'  => 'tembusan',
                    'tipe_field'  => 'textarea',
                    'is_required' => 0,
                    'urutan'      => 99,
                ];
                
                // Tambahkan timestamps hanya jika kolom ada
                if ($hasTimestamps) {
                    $data['created_at'] = now();
                    $data['updated_at'] = now();
                }
                
                DB::table('template_fields')->insert($data);
            }
        }
    }

    public function down()
    {
        // Hapus field tembusan dari semua template
        DB::table('template_fields')->where('nama_field', 'tembusan')->delete();
    }
}