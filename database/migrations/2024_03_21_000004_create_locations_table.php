<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('city_lower')->virtualAs('LOWER(city)')->index();
            $table->string('state');
            $table->string('state_lower')->virtualAs('LOWER(state)')->index();
            $table->string('country');
            $table->string('country_lower')->virtualAs('LOWER(country)')->index();
            $table->timestamps();

            $table->unique(['city', 'state', 'country']);
            $table->index(['city', 'state', 'country']);
            $table->index('city');
            $table->index('state');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
