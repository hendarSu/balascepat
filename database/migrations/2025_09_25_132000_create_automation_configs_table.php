<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->string('client_id');
            $table->string('webhook_url');
            $table->string('secret');
            $table->json('last_response')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['channel_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_configs');
    }
};

