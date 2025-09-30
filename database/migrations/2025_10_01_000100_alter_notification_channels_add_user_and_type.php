<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notification_channels', function (Blueprint $table) {
            // Add user relation (nullable to avoid breaking existing data)
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained()->cascadeOnDelete();

            // Add channel type, default to 'wa_unoffical' for current WA usage
            $table->string('type')->default('wa_unoffical')->after('user_id');

            // Ensure one record per user per type
            $table->unique(['user_id', 'type'], 'notification_channels_user_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('notification_channels', function (Blueprint $table) {
            // Drop unique first
            $table->dropUnique('notification_channels_user_type_unique');
            // Drop FK and columns
            $table->dropForeign('notification_channels_user_id_foreign');
            $table->dropColumn(['user_id', 'type']);
        });
    }
};

