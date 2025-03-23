<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_post_language', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Add unique index to prevent duplicate relationships
            $table->unique(['job_post_id', 'language_id'], 'job_post_language_unique');

            // Add indexes for faster joins
            $table->index('job_post_id', 'job_post_language_job_post_id_index');
            $table->index('language_id', 'job_post_language_language_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_post_language');
    }
};
