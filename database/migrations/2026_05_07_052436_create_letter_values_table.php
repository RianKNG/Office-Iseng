<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLetterValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('letter_values', function (Blueprint $table) {
        $table->id();
        $table->foreignId('letter_id')->constrained('letters')->onDelete('cascade');
        $table->foreignId('field_id')->constrained('template_fields')->onDelete('cascade');
        $table->text('nilai')->nullable();
        $table->timestamp('created_at')->useCurrent();

        // Pastikan tidak ada parent_id di sini
        $table->unique(['letter_id', 'field_id'], 'uk_lv');
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('letter_values');
    }
}
