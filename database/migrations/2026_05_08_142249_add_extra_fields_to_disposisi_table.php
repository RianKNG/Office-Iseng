<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToDisposisiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('disposisi', function (Blueprint $table) {
        $table->text('catatan_respon')->nullable()->after('instruksi');
        $table->text('balasan')->nullable()->after('catatan_respon');
        $table->boolean('is_locked')->default(false);
        $table->integer('urutan_berjenjang')->default(0);
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disposisi', function (Blueprint $table) {
            //
        });
    }
}
