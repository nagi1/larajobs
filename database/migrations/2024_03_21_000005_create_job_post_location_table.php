<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_post_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['job_post_id', 'location_id'], 'job_post_location_unique');
            $table->index('job_post_id', 'job_post_location_job_post_id_index');
            $table->index('location_id', 'job_post_location_location_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_post_location');
    }
};
