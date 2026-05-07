<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Di create_template_fields_table
Schema::create('template_fields', function (Blueprint $table) {
        $table->id();
        // Baris di bawah ini menghubungkan field ke tabel templates (Foreign Key)
        $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
        $table->string('nama_field', 100);
        $table->enum('tipe_field', ['text', 'number', 'date', 'textarea', 'select', 'file']);
        $table->boolean('is_required')->default(false);
        $table->json('opsi_json')->nullable();
        $table->integer('urutan')->default(0);
        $table->timestamp('created_at')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_fields');
    }
}
