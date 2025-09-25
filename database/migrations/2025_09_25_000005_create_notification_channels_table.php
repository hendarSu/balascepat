<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('headers_key')->nullable();
            $table->string('headers_value')->nullable();
            $table->string('auth_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_channels');
    }
};
