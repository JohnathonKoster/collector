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
    'mode' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Release to Start With
    |--------------------------------------------------------------------------
    |
    | The laravel framework release to start splitting Collections from.
    |
    */
    'start_with' => 'v5.3.5',

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Version Tag Source
    |--------------------------------------------------------------------------
    |
    | This option controls which class should be used when constructing
    | the list of all releases for the remote Laravel git repository.
    |
    | Possible values are "GitHub" and "Array"
    */
    'tag_source' => 'GitHub',

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
    'output' => env('SPLIT_DIR_OUTPUT'),

    /*
    |--------------------------------------------------------------------------
    | Source Directory
    |--------------------------------------------------------------------------
    |
    | This option controls the directory that the tool will use to store the
    | Laravel framework source for each version it is attempting to split.
    |
    */
    'source' => env('SPLIT_DIR_SOURCE'),

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
    'publish' => env('SPLIT_DIR_PUBLISH'),

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
        'Collection.php',
    ],


    /*
    |--------------------------------------------------------------------------
    | Replace Classes
    |--------------------------------------------------------------------------
    |
    | This option contains a list of all the class that should automatically
    | be auto-rewritten in the final version of the Collection component.
    |
    */
    'replace_class' => [
        'Illuminate\Database\Eloquent\Collection' => 'Illuminate\Support\Collection',
    ],

    /*
    |--------------------------------------------------------------------------
    | Copy Stubs
    |--------------------------------------------------------------------------
    |
    | This option contains a list of all the stub files that should be copied
    | to the newly created split component. Stubs can be anything, from an
    | environment file common to all components to things like graphics
    | assets and/or documentation files, such as license files, etc.
    |
    */
   'stubs' => [
        'phpunit.xml',
        'collect-logo.png',
        'readme.md',
        'composer.json',
        'tests/bootstrap.php'
    ],

];