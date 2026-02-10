<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->string('full_name');
            $table->enum('distance', ['long', 'medium', 'short']);
            $table->string('finish_time');
            $table->string('age_category');
            $table->unsignedInteger('overall_placement')->nullable();
            $table->unsignedInteger('age_category_placement')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('title');
            $table->index('distance');
            $table->index(['title', 'distance']);
            $table->index('age_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};