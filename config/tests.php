<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | PHPUnit Run Tests Command
    |--------------------------------------------------------------------------
    |
    | This option species the command that should be used to run output test.
    |
    */
    'run' => 'cd "%s" && composer update && php %s/phpunit/phpunit/phpunit --no-globals-backup --bootstrap "%s"',

];