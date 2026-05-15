<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. TABEL CABANGS (untuk Pusat, Cabang, dan Unit)
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_cabang')->unique();
            $table->string('kode')->nullable()->unique();
            $table->enum('tipe', ['pusat', 'cabang', 'unit'])->default('pusat');
            $table->string('alamat')->nullable();
            $table->timestamps();
        });

        // 2. TABEL JABATANS
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->string('level_key'); // kabag, kasie, staff, dll
            $table->integer('urutan')->default(1); // 7=admin, 6=dirut, 5=kabag, dst
            $table->enum('scope', ['pusat', 'cabang', 'unit', 'semua'])->default('semua');
            $table->timestamps();
        });

        // 3. TAMBAH KOLOM FK DI USERS
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('cabang_id')->nullable()->after('struktur')
                  ->constrained('cabangs')->onDelete('set null');
            $table->foreignId('jabatan_id')->nullable()->after('cabang_id')
                  ->constrained('jabatans')->onDelete('set null');
            $table->index(['cabang_id', 'level']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['cabang_id']);
            $table->dropIndex(['cabang_id', 'level']);
            $table->dropColumn(['jabatan_id', 'cabang_id']);
        });
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('cabangs');
    }
};