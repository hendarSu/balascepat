<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add columns individually with existence checks for safety across environments
        if (!Schema::hasColumn('videos', 'progress')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->unsignedInteger('progress')->default(0);
            });
        }
        if (!Schema::hasColumn('videos', 'duration_seconds')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->unsignedInteger('duration_seconds')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'width')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->unsignedInteger('width')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'height')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->unsignedInteger('height')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'poster_path')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->string('poster_path')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'sprite_path')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->string('sprite_path')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'subtitles')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->json('subtitles')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'chapters')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->json('chapters')->nullable();
            });
        }
        if (!Schema::hasColumn('videos', 'watermark')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->json('watermark')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Drop columns if they exist
        $columns = [
            'progress',
            'duration_seconds',
            'width',
            'height',
            'poster_path',
            'sprite_path',
            'subtitles',
            'chapters',
            'watermark',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('videos', $column)) {
                Schema::table('videos', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};

