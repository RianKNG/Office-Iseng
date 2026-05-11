<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUsersEnumAddAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
            DB::statement("ALTER TABLE users MODIFY COLUMN level ENUM('admin','dirut','kabag_kacab','kasubag_kasie','staff')");
        }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
    DB::statement("ALTER TABLE users MODIFY COLUMN level ENUM('dirut','kabag_kacab','kasubag_kasie','staff')");
}
}
