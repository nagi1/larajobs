<?php

use App\Console\Commands\CreateTestDatabaseCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the CreateTestDatabaseCommand
Artisan::registerCommand(new CreateTestDatabaseCommand);
