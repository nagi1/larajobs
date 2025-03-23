<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->timestamps();

            $table->unique(['city', 'state', 'country']);
            $table->index(['city', 'state', 'country']);
            $table->index('city');
            $table->index('state');
            $table->index('country');
        });

        DB::statement('CREATE INDEX locations_city_lower_index ON locations (LOWER(city))');
        DB::statement('CREATE INDEX locations_state_lower_index ON locations (LOWER(state))');
        DB::statement('CREATE INDEX locations_country_lower_index ON locations (LOWER(country))');
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
