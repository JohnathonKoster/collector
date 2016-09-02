<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Split Mode
    |--------------------------------------------------------------------------
    |
    | This option controls how to determine which versions to split. If set
    | to "auto" the splitter will fetch the tags from the remote repo to
    | compare with the split version history. To use "auto", you need
    | to specify a value for the "start_with" option below. To use
    | the "manual" mode, you must list all versions to split in
    | the "versions" option below; specifying the output dir.
    |
    */
    'mode' => 'manual',

    /*
    |--------------------------------------------------------------------------
    | Release to Start With
    |--------------------------------------------------------------------------
    |
    | The laravel framework release to start splitting Collections from.
    |
    */
    'start_with' => 'v5.2.32',

    /*
    |--------------------------------------------------------------------------
    | Remote Versions to Split
    |--------------------------------------------------------------------------
    |
    | This option contains a list of all the remote branches the splitter
    | tool should attempt to split the Collection library from. Using
    | the keys, the splitter will create temporary directories for
    | each remote branch. The value for the branch specifies an
    | output directory name, where the generated library can
    | be found, and then committed to the new repository.
    |
    */
    'versions' => [
        'v5.3.4'       => '5.3.4',
        'v5.3.3'       => '5.3.3',
        'v5.2.45'      => '5.2.45',
        'v5.0.21'      => '5.0.21',
        'v4.1.31'      => '4.1.31',
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Directory
    |--------------------------------------------------------------------------
    |
    | This option controls the output directory for the split Collection
    | repositories. All targeted branches will get a sub-directory in
    | the output directory. Make sure the user running the process
    | has read and write permissions to this directory. Windows
    | users make sure to run with elevated user permissions.
    |
    */
    'output' => 'output',

    /*
    |--------------------------------------------------------------------------
    | Source Directory
    |--------------------------------------------------------------------------
    |
    | This option controls the directory that the tool will use to store the
    | Laravel framework source for each version it is attempting to split.
    |
    */
    'source' => 'tmp',

    /*
    |--------------------------------------------------------------------------
    | Publish Directory
    |--------------------------------------------------------------------------
    |
    | This option controls the directory that all the generated collection
    | components will be copied to after a successful split. Also, this
    | directory should be the final git repository that git commands
    | will be executed when publishing the newly split version(s).
    |
    */
    'publish' => 'publish',

    /*
    |--------------------------------------------------------------------------
    | Starting Classes
    |--------------------------------------------------------------------------
    |
    | This option contains a list of all the class files that the splitter
    | looks for at first. The splitter should be capable of finding the
    | dependencies for the classes listed here, so don't list all of
    | the classes, as you might confuse the splitter & analyzers.
    |
    */
    'classes' => [
        'Collection.php'
    ],


    'replace_class' => [
        'Illuminate\Database\Eloquent\Collection' => 'Illuminate\Support\Collection',
        'Test\Class\Name' => 'Illuminate\Database\Eloquent\Collection',
    ],

];