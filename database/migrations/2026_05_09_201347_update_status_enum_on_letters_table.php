<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateStatusEnumOnLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        DB::statement("ALTER TABLE letters MODIFY COLUMN status ENUM('draft', 'menunggu_verifikasi', 'diproses', 'disetujui', 'ditolak', 'selesai', 'arsip') DEFAULT 'menunggu_verifikasi'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE letters MODIFY COLUMN status ENUM('draft', 'menunggu_verifikasi', 'disetujui', 'ditolak', 'arsip') DEFAULT 'menunggu_verifikasi'");
    }
}
