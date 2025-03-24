<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTestDatabaseCommand extends Command
{
    protected $signature = 'db:create-test';

    protected $description = 'Create the test database';

    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $testDatabase = 'larajobs_testing';

        config(['database.connections.mysql.database' => null]);

        try {
            $this->info("Creating database {$testDatabase}...");

            DB::statement("DROP DATABASE IF EXISTS {$testDatabase}");
            DB::statement("CREATE DATABASE {$testDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $this->info("Database {$testDatabase} created successfully.");
        } catch (\Exception $e) {
            $this->error('Failed to create database: '.$e->getMessage());
        } finally {
            config(['database.connections.mysql.database' => $database]);
        }
    }
}
