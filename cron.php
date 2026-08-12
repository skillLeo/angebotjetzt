<?php

/**
 * Scheduler entry point for hosts whose cron UI takes a script path but no
 * arguments (ALL-INKL among them), where `artisan schedule:run` cannot be
 * expressed directly. Call this file once a minute instead.
 *
 * It lives beside artisan rather than in public/, so it is not reachable over
 * the web once the domain points at public/.
 */

require __DIR__ . '/vendor/autoload.php';

$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('schedule:run');

echo $kernel->output();

exit($status);
