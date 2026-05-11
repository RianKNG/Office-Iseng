<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Update ENUM 'level' untuk menambahkan 'admin'
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN level ENUM('admin','dirut','kabag_kacab','kasubag_kasie','staff') 
            NOT NULL DEFAULT 'staff'
        ");

        // 2. Drop generated column 'level_urutan' dulu (jika ada)
        DB::statement("
            ALTER TABLE users 
            DROP COLUMN level_urutan
        ");

        // 3. Tambah kembali sebagai GENERATED COLUMN dengan CASE yang include 'admin'
        DB::statement("
            ALTER TABLE users 
            ADD COLUMN level_urutan INT GENERATED ALWAYS AS (
                CASE level
                    WHEN 'admin' THEN 5
                    WHEN 'dirut' THEN 4
                    WHEN 'kabag_kacab' THEN 3
                    WHEN 'kasubag_kasie' THEN 2
                    WHEN 'staff' THEN 1
                    ELSE 0
                END
            ) STORED AFTER level
        ");
    }

    public function down()
    {
        // 1. Drop generated column
        DB::statement("
            ALTER TABLE users 
            DROP COLUMN level_urutan
        ");

        // 2. Kembalikan ENUM ke nilai semula (tanpa 'admin')
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN level ENUM('dirut','kabag_kacab','kasubag_kasie','staff') 
            NOT NULL DEFAULT 'staff'
        ");

        // 3. Tambah kembali generated column tanpa 'admin'
        DB::statement("
            ALTER TABLE users 
            ADD COLUMN level_urutan INT GENERATED ALWAYS AS (
                CASE level
                    WHEN 'dirut' THEN 4
                    WHEN 'kabag_kacab' THEN 3
                    WHEN 'kasubag_kasie' THEN 2
                    WHEN 'staff' THEN 1
                    ELSE 0
                END
            ) STORED AFTER level
        ");
    }
};