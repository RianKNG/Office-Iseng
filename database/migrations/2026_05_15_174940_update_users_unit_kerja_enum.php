<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUsersUnitKerjaEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    DB::statement("ALTER TABLE users MODIFY COLUMN unit_kerja ENUM('keuangan','pelayanan','teknikprod','perencanaan','umum','it') NOT NULL DEFAULT 'umum';");
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
