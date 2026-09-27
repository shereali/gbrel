<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:reset-password {email? : The email of the admin user} {--password= : The new password}', function () {
    $this->call('admin:create', [
        'email' => $this->argument('email'),
        '--password' => $this->option('password'),
        '--force' => true,
    ]);
})->purpose('Reset an administrator account password');

