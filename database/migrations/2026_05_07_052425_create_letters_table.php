<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('letters', function (Blueprint $table) {
        $table->id();
        $table->foreignId('template_id')->constrained('templates');
        $table->string('nomor_surat', 100)->index();
        $table->date('tanggal')->nullable();
        $table->text('perihal')->nullable();
        $table->enum('jenis', ['masuk', 'keluar', 'nota']);
        $table->enum('status', ['draft', 'menunggu_verifikasi', 'disetujui', 'ditolak', 'arsip'])->default('draft');
        $table->tinyInteger('current_level')->default(1);
        
        // Relasi ke pembuat surat dan penyetuju (tabel users)
        $table->foreignId('created_by')->constrained('users');
        $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
        
        $table->string('file_path', 255)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('letters');
    }
}
