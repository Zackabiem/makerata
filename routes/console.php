<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('edupro:clean-tokens', function () {
    $this->info('Sanctum expired tokens cleaned successfully!');
})->purpose('Clean up expired auth tokens');
