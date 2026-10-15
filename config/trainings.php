<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Completion Threshold
    |--------------------------------------------------------------------------
    |
    | Percentage of the training video an employee must actually watch for
    | the training to be considered completed.
    |
    */

    'completion_threshold' => (int) env('TRAINING_COMPLETION_THRESHOLD', 90),

    /*
    |--------------------------------------------------------------------------
    | Invitation Lifetime
    |--------------------------------------------------------------------------
    |
    | Number of days an employee invitation link stays valid. An expired
    | invitation can be sent again from the administration panel.
    |
    */

    'invitation_expires_in_days' => (int) env('TRAINING_INVITATION_EXPIRES_IN_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Login Link Lifetime
    |--------------------------------------------------------------------------
    |
    | Number of minutes a login link requested from the portal login page
    | stays valid. Each link can only be used once.
    |
    */

    'login_link_expires_in_minutes' => (int) env('TRAINING_LOGIN_LINK_EXPIRES_IN_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Administrator Login Link Lifetime
    |--------------------------------------------------------------------------
    |
    | Number of hours a login link copied from the administration panel stays
    | valid. It is also single-use and replaces any link sent earlier.
    |
    */

    'administrator_login_link_expires_in_hours' => (int) env('TRAINING_ADMINISTRATOR_LOGIN_LINK_EXPIRES_IN_HOURS', 24),

];
