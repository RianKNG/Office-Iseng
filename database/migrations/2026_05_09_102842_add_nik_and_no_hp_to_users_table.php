<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNikAndNoHpToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
        // Tambahkan nullable() dan HAPUS unique() untuk sementara
        $table->string('nik', 16)->nullable()->after('id');
        $table->string('no_hp', 20)->nullable()->after('nik');
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['nik', 'no_hp']);
    });
    }
}
