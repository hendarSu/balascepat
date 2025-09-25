<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('text');
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->string('client_id')->nullable();
            $table->string('external_id')->nullable(); // remote broadcast id
            $table->json('recipients')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->json('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
