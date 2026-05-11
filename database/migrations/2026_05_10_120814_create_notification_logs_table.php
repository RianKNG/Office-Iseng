<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('letter_id')->nullable()->constrained('letters')->onDelete('set null');
            $table->foreignId('disposisi_id')->nullable()->constrained('disposisi')->onDelete('set null');
            $table->enum('channel', ['log', 'database', 'whatsapp', 'email'])->default('database');
            $table->text('pesan');
            $table->enum('status', ['terkirim', 'gagal', 'error', 'simulasi'])->default('terkirim');
            $table->json('response')->nullable(); // Response dari API WA/Email
            $table->timestamp('read_at')->nullable(); // Untuk in-app notification
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['disposisi_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};