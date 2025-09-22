<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'video_view')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('video_view', 10)->default('grid')->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'video_view')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('video_view');
            });
        }
    }
};

