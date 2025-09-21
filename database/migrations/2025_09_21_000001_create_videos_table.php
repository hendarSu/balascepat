<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('status')->default('uploaded'); // uploaded|processing|completed|failed
            $table->boolean('is_public')->default(false);
            $table->string('hls_path')->nullable();
            $table->string('encryption_type')->nullable(); // single|rotating
            $table->json('encryption_keys')->nullable();
            $table->string('export_token')->nullable()->index();
            $table->boolean('allow_export')->default(false);
            $table->boolean('allow_embed')->default(false);
            $table->timestamp('export_expires_at')->nullable();
            $table->json('embed_settings')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};

