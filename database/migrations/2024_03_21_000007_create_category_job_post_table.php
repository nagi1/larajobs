<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_job_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['job_post_id', 'category_id'], 'category_job_post_unique');
            $table->index('job_post_id', 'category_job_post_job_post_id_index');
            $table->index('category_id', 'category_job_post_category_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_job_post');
    }
};
