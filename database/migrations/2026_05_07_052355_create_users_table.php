<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('nama_lengkap', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->enum('level', ['staff', 'kasubag_kasie', 'kabag_kacab', 'dirut'])->default('staff');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // Generated Virtual Column untuk level_urutan
        DB::statement("
            ALTER TABLE users 
            ADD level_urutan TINYINT GENERATED ALWAYS AS (
                CASE level 
                    WHEN 'staff' THEN 1 
                    WHEN 'kasubag_kasie' THEN 2 
                    WHEN 'kabag_kacab' THEN 3 
                    WHEN 'dirut' THEN 4 
                END
            ) VIRTUAL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};