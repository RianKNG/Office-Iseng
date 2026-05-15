<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ===== ISI TABEL CABANGS =====
        
        // 1. Kantor Pusat
        $pusatId = DB::table('cabangs')->insertGetId([
            'nama_cabang' => 'Kantor Pusat',
            'kode' => 'PUSAT',
            'tipe' => 'pusat',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Cabang-cabang yang sudah ada
        $existingCabang = DB::table('users')
            ->where('struktur', 'cabang')
            ->distinct()
            ->pluck('unit_kerja');
        
        $counter = 1;
        foreach ($existingCabang as $unit) {
            DB::table('cabangs')->insert([
                'nama_cabang' => 'Cabang ' . ucfirst($unit),
                'kode' => 'CAB' . str_pad($counter, 2, '0', STR_PAD_LEFT),
                'tipe' => 'cabang',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $counter++;
        }

        // 3. Unit-unit (jika ada yang tipe unit)
        DB::table('cabangs')->insert([
            'nama_cabang' => 'Unit Operasional 1',
            'kode' => 'UNIT01',
            'tipe' => 'unit',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ===== ISI TABEL JABATANS =====
        
        $jabatans = [
            ['nama' => 'Direktur Utama', 'level_key' => 'dirut', 'urutan' => 6, 'scope' => 'pusat'],
            ['nama' => 'Kepala Bagian Keuangan', 'level_key' => 'kabag', 'urutan' => 5, 'scope' => 'pusat'],
            ['nama' => 'Kepala Bagian Pelayanan', 'level_key' => 'kabag', 'urutan' => 5, 'scope' => 'pusat'],
            ['nama' => 'Kepala Sub Bagian', 'level_key' => 'kasubag', 'urutan' => 3, 'scope' => 'pusat'],
            ['nama' => 'Kepala Cabang', 'level_key' => 'kacab', 'urutan' => 5, 'scope' => 'cabang'],
            ['nama' => 'Kepala Seksi', 'level_key' => 'kasie', 'urutan' => 3, 'scope' => 'cabang'],
            ['nama' => 'Staff', 'level_key' => 'staff', 'urutan' => 1, 'scope' => 'semua'],
        ];

        foreach ($jabatans as $j) {
            DB::table('jabatans')->insert([
                'nama_jabatan' => $j['nama'],
                'level_key' => $j['level_key'],
                'urutan' => $j['urutan'],
                'scope' => $j['scope'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // ===== UPDATE USERS DENGAN ID BARU =====
        
        $users = DB::table('users')->get();
        
        foreach ($users as $u) {
            $updateData = [];
            
            // Set cabang_id
            if ($u->struktur === 'pusat') {
                $updateData['cabang_id'] = $pusatId;
            } else {
                $cabangId = DB::table('cabangs')
                    ->where('tipe', 'cabang')
                    ->value('id');
                $updateData['cabang_id'] = $cabangId;
            }
            
            // Set jabatan_id
            $jabatanId = DB::table('jabatans')
                ->where('level_key', $u->level)
                ->whereIn('scope', [$u->struktur, 'semua'])
                ->value('id');
            $updateData['jabatan_id'] = $jabatanId;
            
            // Update user
            if (!empty($updateData)) {
                DB::table('users')
                    ->where('id', $u->id)
                    ->update($updateData);
            }
        }
    }

    public function down()
    {
        DB::table('users')->update([
            'cabang_id' => null,
            'jabatan_id' => null
        ]);
    }
};