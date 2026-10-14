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

];
