<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable()->index();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

