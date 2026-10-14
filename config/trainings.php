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

];
