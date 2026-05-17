<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up()
    // {
    //     Schema::create('notifikasis', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id');
    //         $table->unsignedBigInteger('disposisi_id')->nullable();
    //         $table->text('message');
    //         $table->boolean('is_read')->default(false);
    //         $table->timestamps();
            
    //         $table->index(['user_id', 'disposisi_id']);
    //     });
    // }
    public function up()
{
    // Tambahkan baris IF ini untuk membungkus perintah Schema::create
    if (!Schema::hasTable('notifikasis')) {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('disposisi_id')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'disposisi_id']);
        });
    }
}

    public function down()
    {
        Schema::dropIfExists('notifikasis');
    }
};