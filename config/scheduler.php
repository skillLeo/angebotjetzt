<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scheduler trigger token
    |--------------------------------------------------------------------------
    |
    | Shared hosting often provides no shell and no argument-taking cron, only
    | a service that fetches a URL on a timer. This secret turns the scheduler
    | into such a URL. Leave it empty to disable the route entirely, which is
    | the right setting anywhere a real `artisan schedule:run` cron exists.
    |
    */

    'token' => env('SCHEDULER_TOKEN'),

];
