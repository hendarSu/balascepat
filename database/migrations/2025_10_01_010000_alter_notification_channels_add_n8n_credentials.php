<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notification_channels', function (Blueprint $table) {
            $table->string('n8n_username')->nullable()->after('auth_type');
            $table->text('n8n_password')->nullable()->after('n8n_username');
        });
    }

    public function down(): void
    {
        Schema::table('notification_channels', function (Blueprint $table) {
            $table->dropColumn(['n8n_username', 'n8n_password']);
        });
    }
};

