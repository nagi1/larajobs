<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->unique('name');
            $table->index('name', 'categories_name_index');
        });

        DB::statement('CREATE INDEX categories_name_lower_index ON categories (LOWER(name))');
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
