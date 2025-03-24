<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_attribute_values', function (Blueprint $table) {
            // Add index on job_post_id and attribute_id combination for faster filtering
            $table->index(['job_post_id', 'attribute_id'], 'jav_job_attr_idx');

            // Add index on attribute_id and value for faster filtering by attribute values
            // Limited to 191 characters for compatibility with MySQL's utf8mb4 encoding
            $table->index(['attribute_id', 'value(191)'], 'jav_attr_value_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_attribute_values', function (Blueprint $table) {
            $table->dropIndex('jav_job_attr_idx');
            $table->dropIndex('jav_attr_value_idx');
        });
    }
};
