<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisposisiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('disposisi', function (Blueprint $table) {
        $table->id();
        // Menghubungkan disposisi ke surat tertentu
        $table->foreignId('letter_id')->constrained('letters')->onDelete('cascade');
        
        // Parent ID untuk disposisi berantai (misal: dari Direktur ke Kabag ke Staff)
        $table->foreignId('parent_id')->nullable()->constrained('disposisi')->onDelete('set null');
        
        // Aktor yang terlibat
        $table->foreignId('dari_user_id')->constrained('users');
        $table->foreignId('ke_user_id')->constrained('users');
        
        $table->text('instruksi')->nullable();
        $table->enum('prioritas', ['biasa', 'penting', 'segera', 'rahasia'])->default('biasa');
        $table->enum('status', ['pending', 'dibaca', 'diproses', 'diteruskan', 'selesai'])->default('pending');
        $table->date('deadline')->nullable();
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
        Schema::dropIfExists('disposisi');
    }
}
