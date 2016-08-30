<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Git Clone Command
    |--------------------------------------------------------------------------
    |
    | This option specifies the command that should be issued when the
    | splitter needs to clone a remote Laravel framework branch. To
    | customize this command, make sure the first placeholder is
    | the remote branch to clone from, and that the second is
    | used to specify the location to clone the branch to.
    |
    */
    'clone' => 'git clone -b "%s" --single-branch --depth 1 https://github.com/laravel/framework.git %s',

];