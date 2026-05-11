<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStrukturUnitToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('struktur', ['pusat', 'cabang'])->default('pusat')->after('jabatan');
            $table->enum('unit_kerja', [
                'keuangan', 'pelayanan', 'teknikprod', 'perencanaan', 'umum', 'spi'
            ])->default('umum')->after('struktur');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['struktur', 'unit_kerja']);
        });
    }
}
