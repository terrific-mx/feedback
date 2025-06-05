<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feedback Board Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains various configuration options for the feedback board.
    |
    */

    'admin_emails' => explode(',', env('ADMIN_EMAILS', 'oliver@example.com')),
];
