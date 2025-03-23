<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Base data that other models depend on
        $this->call([
            CategorySeeder::class,
            LocationSeeder::class,
            LanguageSeeder::class,
            AttributeSeeder::class,
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create job posts with relationships
        $this->call([
            JobPostSeeder::class,
            JobAttributeValueSeeder::class,
        ]);
    }
}
