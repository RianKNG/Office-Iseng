<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ 1. Expand ENUM level (tambah nilai baru, jangan hapus lama dulu)
        DB::statement("
            ALTER TABLE `users` 
            MODIFY `level` ENUM(
                'admin', 'dirut', 
                'kabag_kacab', 'kasubag_kasie',
                'kabag', 'kacab', 'kanit',
                'kasubag', 'kasie',
                'staff'
            ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff'
        ");

        // ✅ 2. Update data dari nilai lama → baru
        DB::update("UPDATE `users` SET `level` = 'kabag' WHERE `level` = 'kabag_kacab' AND `struktur` = 'pusat'");
        DB::update("UPDATE `users` SET `level` = 'kacab' WHERE `level` = 'kabag_kacab' AND `struktur` = 'cabang'");
        DB::update("UPDATE `users` SET `level` = 'kasubag' WHERE `level` = 'kasubag_kasie' AND `struktur` = 'pusat'");
        DB::update("UPDATE `users` SET `level` = 'kasie' WHERE `level` = 'kasubag_kasie' AND `struktur` = 'cabang'");

        // ✅ 3. Shrink ENUM (hapus nilai lama)
        DB::statement("
            ALTER TABLE `users` 
            MODIFY `level` ENUM(
                'admin', 'dirut', 
                'kabag', 'kacab', 'kanit',
                'kasubag', 'kasie',
                'staff'
            ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff'
        ");

        // ✅ 4. Tambah ke_user_id di letters (jika belum ada)
        if (!Schema::hasColumn('letters', 'ke_user_id')) {
            Schema::table('letters', function (Blueprint $table) {
                $table->unsignedBigInteger('ke_user_id')->nullable()->after('created_by');
                $table->foreign('ke_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // Rollback letters
        if (Schema::hasColumn('letters', 'ke_user_id')) {
            Schema::table('letters', function (Blueprint $table) {
                $table->dropForeign(['ke_user_id']);
                $table->dropColumn('ke_user_id');
            });
        }

        // Rollback ENUM
        DB::update("UPDATE `users` SET `level` = 'kabag_kacab' WHERE `level` IN ('kabag', 'kacab')");
        DB::update("UPDATE `users` SET `level` = 'kasubag_kasie' WHERE `level` IN ('kasubag', 'kasie')");
        
        DB::statement("
            ALTER TABLE `users` 
            MODIFY `level` ENUM(
                'admin', 'dirut', 
                'kabag_kacab', 'kasubag_kasie',
                'staff'
            ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff'
        ");
    }
};