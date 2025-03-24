<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            // Add index on job_post_id and attribute_id combination for faster filtering
            $table->index(['job_post_id', 'attribute_id']);
            // Add index on attribute_id and value for faster filtering by attribute values
            $table->index(['attribute_id', 'value(191)']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_attribute_values');
    }
};
