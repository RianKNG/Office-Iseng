<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID Penerima
            $table->unsignedBigInteger('disposisi_id')->nullable(); // ID Disposisi
            
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // ✅ Index agar query cepat (tanpa constraint FK yang rigid)
            $table->index('user_id');
            $table->index('disposisi_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifikasis');
    }
};