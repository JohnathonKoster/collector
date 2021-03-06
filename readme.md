![](https://cloud.githubusercontent.com/assets/5232890/18241574/88bbf030-7317-11e6-90f2-92af52c626e9.png)

The `collector` tool attempts to automate the process of splitting the `Illuminate\Support\Collection` component from the Laravel code-base. It strives to solve this issue: https://github.com/tightenco/collect/issues/2. The output of this utility can be viewed at https://github.com/JohnathonKoster/collector-output-test (it is not recommended to use the releases within the `collector-output-test` repository directly; instead, use Tighten Co's repository).

* [Download and Installation](https://github.com/JohnathonKoster/collector/blob/master/readme.md#download-and-installation)
* [Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#configuration)
    * [Environment Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#configuration)
    * [git Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#configuration)
        * [`git.clone` Configuration] (https://github.com/JohnathonKoster/collector/blob/master/readme.md#gitcone-configgitphp-config-file-or-git_clone-env-file)
        * [`git.publish` Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#gitpublish-configgitphp-config-file-or-git_publish-env-file)
        * [`git.update` Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#gitupdate-configgitphp-config-file-or-git_update-env-file)
    * [Tests Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#tests-configuration)
    * [Split Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#split-configuration)
        * [Operating Mode](https://github.com/JohnathonKoster/collector/blob/master/readme.md#splitter-operation-mode)
        * [Starting Classes](https://github.com/JohnathonKoster/collector/blob/master/readme.md#splitter-starting-classes)
        * [Replacing Classes](https://github.com/JohnathonKoster/collector/blob/master/readme.md#splitter-replace-classes)
        * [Stub Files](https://github.com/JohnathonKoster/collector/blob/master/readme.md#splitter-stubs)
        * [Directory Configuration](https://github.com/JohnathonKoster/collector/blob/master/readme.md#splitter-directories)
* [Using the `collect` Command](https://github.com/JohnathonKoster/collector/blob/master/readme.md#using-the-collect-command)
    * [`collect` Flags](https://github.com/JohnathonKoster/collector/blob/master/readme.md#collect-flags)
*  [Using the `test:output` Command](https://github.com/JohnathonKoster/collector/blob/master/readme.md#using-the-collecttags-command)
*  [Clearing the Caches](https://github.com/JohnathonKoster/collector/blob/master/readme.md#clearing-the-caches)
*  [License](https://github.com/JohnathonKoster/collector/blob/master/readme.md#license)

## Download and Installation

The easiest way to download the Collector utility is just to clone the repository:

```
git clone https://github.com/JohnathonKoster/collector.git
```

After you have obtained the source, you must install the Composer dependencies using `composer install`. Composer will install things such as the Symfony console and process components, a PHP Parser, a GitHub API and some filesystem testing utilities.

## Configuration

All of Collector's configuration files live within the `config/` directory. There are three main groups of configuration items:

* __git__: These settings control the exact git commands that will be ran when the Collector needs to retrieve a version of the Laravel framework, when it needs to commit and tag new split components and when it needs to push the split components to the destination repository.
* __split__: These settings control various aspects of the Collector utility itself. Things such as the particular versions of the Laravel framework to target, and what class should be split from the code-base can be found here.
* __tests__: By default, Collector will ensure that all the unit tests pass for the split component before it is published to the destination repository. The PHPUnit command to issue can be configured here.

### Environment Configuration

To make it easier to run the Collector utility across different environments, the Collector utility uses the [DotEnv](https://github.com/vlucas/phpdotenv) PHP library to make it easier to manage configuration values for different environments.

The `.env.example` file shows all of the environment variables that _must_ be set in order for the Collector utility to run. Create a copy of the `.env.example` file and rename it `.env` and supply the correct values for your environment.

### git Configuration

There are only three git settings that need to be configured. Each of them are important to ensure the successful split of the Illuminate Collection component.

#### `git.cone` (`config/git.php` config file) or `GIT_CLONE` (`.env` file)

The `clone` setting is used to specify the command that is used to clone versions of the [`laravel/framework`]http://github.com/laravel/framework) for the Collector utility. You can customize this command to suite your specific environment, but it __must__ accomplish the following tasks:

* Clone the requested framework version into the correct temporary directory.

The following example is the default setting that can be found in the `.env.example` file:

```
GIT_CLONE="git clone -b \"@version@\" --single-branch --depth 1 https://github.com/laravel/framework.git \"@source@\""
```

You may notice that the command contains placeholder variables (surrounded by the `@` sigil). These placeholders will be replaced by the Collector utility to provide your command with the corresponding values. The following placeholders can be used when constructing your own command:

| Placeholder | Description | Example |
|---|---|---|
| `@version@` | The version of the Laravel framework the Collector utility is current processing. | `v5.3.6` |
| `@source@` | The path to the expected temporary directory that the Laravel framework version should be cloned into. | `/source/path/v5.3.6` |

#### `git.publish` (`config/git.php` config file) or `GIT_PUBLISH` (`.env` file)

The `publish` setting is used to specify the command that is used to commit __and__ tag the changes made for each version of the Illuminate Collection component that is split from the Laravel framework code-base. This command can be customized, but must accomplish the following tasks:

> Note: The Collector utility will clear all of the files in the git repository on each iteration to ensure that only the files required for the current Collection component version are present. This clearing operation is __not__ destructive to the actual git repository.

* Add the necessary files and commit them;
* Create a new tag for the previous commit.

The following example is the default setting that can be found in the `.env.example` file:

```
GIT_PUBLISH="git -C \"@publishDir@\" add --all && git -C \"@publishDir@\" commit -m \"Updated to @version@ changes.\" && git -C \"@publishDir@\" tag -a @version@ -m \"Updated to @version@ changes\""
```

> An important thing to note is that the default command specifies the git directory (via the `-C` switch). This is important to ensure that git commands are issued only against the target git repository.

The following placeholders can be used when constructing your own command:

| Placeholder | Description | Example |
|---|---|---|
| `@version@` | The version of the Laravel framework the Collector utility is current processing. | `v5.3.6` |
| `@publishDir@` | The path to the target git repository. | `/path/to/repository` |

#### `git.update` (`config/git.php` config file) or `GIT_UPDATE` (`.env` file)

The `update` setting is used to specify the command that is used to update the target git repository. This is generally done via a `git push`. Since the Collector utility makes extensive use of tags (to create releases on GitHub), the `update` command should also push the tags to the remote git repository.

The following example is the default setting that can be found in the `.env.example` file:

```
GIT_UPDATE="git -C \"@publishDir@\" push --follow-tags"
```

The following placeholders can be used when constructing your own command:

| Placeholder | Description | Example |
|---|---|---|
| `@version@` | The version of the Laravel framework the Collector utility is current processing. | `v5.3.6` |
| `@publishDir@` | The path to the target git repository. | `/path/to/repository` |

### Tests Configuration

There is only one test setting that needs to be configured. The Collector utility uses this command to run the tests on the split Collection component. This setting is important because the Collector utility will ensure that all the tests pass for the split Collection component before it adds the new version to the target git repository.

#### `tests.run` (`config/tests.php` config file) or `TEST_RUN` (`.env` file)

The `run` setting is used to specify the command that the Collector utility will use to run the tests on each split Collection component. You can customize the command to meet the needs of your specific environment, but it must accomplish the following tasks:

* Update the dependencies for each Collection component;
* Run the PHPUnit unit tests for each Collection component.

The following example is the default setting that can be found in the `.env.example` file:

```
TEST_RUN="cd \"@outputDir@\" && composer update && php @vendor@/phpunit/phpunit/phpunit --no-globals-backup --bootstrap \"@bootstrap@\""
```

The following placeholders can be used when building your own command:

| Placeholder | Description | Example |
|---|---|---|
| `@bootstrap@` | The path to the recommended test `bootstrap.php` file. | `collector/storage/tests/bootstrap.php` |
| `@outputDir@` | The path to the output directory for the current Collection version. | `/output/path/v5.3.6` |
| `@vendor@` | The path to the shared vendor folder for all Collection components. | `collector/vendor_test` |
| `@version@` | The version of the Laravel framework the Collector utility is current processing. | `v5.3.6` |

When constructing your own command, it is highly recommended that you use the shared `vendor_test` directory (use the `@vendor@` placeholder and Collector will figure out the location for you) as well as the shared `bootstrap.php` test bootstrap file (again, use the `@bootstrap@` placeholder). This will let all split versions of the Illuminate Collection share dependencies and greatly speed up the testing process.

### Split Configuration

The `config/split.php` configuration file contains many different settings that can be used to alter the behavior of the Collector utility. The following sections will go through each of these settings and explain how they work, when you should use them (and when you shouldn't!).

#### Splitter Operation Mode

The splitter operation mode is set by modifying the `split.mode` configuration value. It can be either `auto` or `manual`. We will discuss the `manual` mode first.

The manual mode will instruct the Collector utility to only split the versions you specify in the `split.versions` configuration entry. It is called _manual_ mode because you have to manage the list of versions to split yourself. This can be beneficial, especially when working with existing git repositories. This method also allows customization of the output version name. When specifying the versions to manually split, you must supply the target Laravel framework version as the key and the name of the output version as the value (in most situations, these will be the same):

```php

    // ...

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
        'v5.3.6' => 'v5.3.6'
    ],

    // ...
```

Running `php collector collect` will then split all the Collection components for the listed Laravel framework versions.

When using the `auto` mode, you must indicate which version of the Laravel framework you would like the splitter to start with when splitting the Illuminate Collection components from the Laravel code-base. This is done by supplying a string value for the `split.start_with` configuration entry:


```php

// ...

    /*
    |--------------------------------------------------------------------------
    | Release to Start With
    |--------------------------------------------------------------------------
    |
    | The laravel framework release to start splitting Collections from.
    |
    */
    'start_with' => 'v5.3.5',

    // ...
```

> __NOTE__: The version specified in the `split.start_with` configuration entry will also be split. For example, if you already had a previous version of the Illuminate Collection component for `v5.3.5`, you might want to start with `v5.3.6` instead.

The `split.tag_source` configuration entry is also something to consider when using the `auto` split operation mode. The `split.tag_source` determines where the Collector utility will get it's list of versions for the Laravel framework. It has two possible value:

* __`GitHub`__: Uses the GitHub API to determine the most recent versions. Most accurate, but slightly slower and may not contain all of the older Laravel framework versions. Use this option to target _recent_ versions of the Laravel framework.
* __`Array`__: Uses a pre-built list of Laravel framework versions. Much faster than the `GitHub` option, but has the potential to have a slight update delay after new Laravel framework versions are released. Use this option to target _all_ versions of the Laravel framework.


#### Splitter Starting Classes

The `split.classes` configuration entry determines which class the Collector utility should attempt to split. Generally, you will not have to change this list. However, if you receive errors about the `Arr.php` file missing in the generated output, you may add it here. The Collector utility is, for the most part, fully capable of resolving class dependencies all by itself.

#### Splitter Replace Classes

The `split.replace_class` configuration entry contains a list of class names that should automatically be transposed in the split Illuminate Collection component. Like the `split.classes` option, you will generally not have to worry about this configuration option. However, as an example of what it does, by default it will instruct the Collector utility to replace all occurences of `Illuminate\Database\Eloquent\Collection` with `Illuminate\Support\Collection`.

#### Splitter Stubs

The `split.stubs` configuration entry contains a list of files that should be copied to _every_ split Illuminate Collection component. Use this option to specify things such as image assets, readme files, licenses, etc.

All stubs must be stored within the `/storage/stubs/` directory; when adding items to the `split.stubs` entry you must specify the path __relative__ to the storage directory.

#### Splitter Directories

There are three directories that need to be configured to use the Collector utility. These directories should be configured using the `.env` environment configuration file. __Make sure to specify the full path to these directories!__.

The directories to configure are:

* __`SPLIT_DIR_OUTPUT`__: The directory where all of the generated Illuminate Collection components will be stored. This directory is where the `GIT_CLONE` command is executed.
* __`SPLIT_DIR_SOURCE`__: The directory where all of the required Laravel framework versions will be cloned into. The `TEST_RUN` command is executed within this directory.
* __`SPLIT_DIR_PUBLISH`__: The directory where all of the publishing actions will be performed. This is generally an existing git repository; this is where the `GIT_PUBLISH` and `GIT_UPDATE` commands are executed.

## Using the `collect` Command

The `collect` command is used to perform the actual process of splitting out all of the required versions of the Illuminate Collection component. It is a simple command to use, and the most basic way to use it is by simply calling it like so:

```
php collector collect
```

When the command is executing, the Collector utility will check which versions of the Illuminate Collection components need to be split (the Collector maintains a history of versions previously split). When it has found a version that it needs to split, it will do the following actions in order:

1. Obtain a copy of the Laravel framework for the specified version (such as `v5.3.6`.
2. Copy known files to the destination directory (things such as the test suites and `.gitignore` files). If this operation fails, it is most likely that a problem occurred during step one. The Collector will attempt steps one and two twice before failing.
3. Analyze the Laravel framework source code (obtained in step one) to find any additional dependencies that will be required in the final split version (things like `Arr.php` and `Traits/Macroable.php` are discovered in this step).
4. The Collector will analyze the dependencies discovered in step three to find any helper function calls (it will analyze the `src/Illuminate/Support/helpers.php` file in the Laravel code-base to get a list of helper functions to search for).
5. The Collector utility will write a new `helpers.php` file in the destination directory containing only the helper functions actually called by the split Collection component.
6. Add the version to the split history.
7. Run the tests for the newly created Illuminate Collection component.
8. If tests pass, the Collector will "publish" the newly created Illuminate Collection component to the destination git repository (configured via the `split.publish` configuration entry).

The Collector utility will repeat those steps for _every_ version that needs to be split.

### `collect` Flags

The `collect` command accepts a number of different flags that can be used to alter how the utility runs. The following table explains each of the available flags.

| Name | Shortcut | Description |
|---|---|---|
| `git` | `-g` | Forces the Collector utility to clone each version of Laravel framework needed each time it is ran. By default, the Collector utility will not clone copies of the Laravel framework if a version already exists in the temporary source directory (configured via the `split.source` configuration entry). |
| `catchup` | `-c` | Similar to the `-g` flag, the `-c` flag instructs the Collector utility to only clone copies of the Laravel framework that it has not obtained yet during previous split operations. |
| `force` | `-f` | Useful for debugging your environment configuration, the `-f` flag will cause the Collector utility to ignore the split history and run the split process against all configured versions of the Laravel framework when in __automatic__mode. |
| `verbose` | `-v` | When in verbose mode, the Collector utility will display a large amount of detailed information related to the split process. Useful for debugging, or when you want to see the terminal explode with output. |

The most common way to run the `collect` command is:

```
php collector collect -g -c
```

## Using the `collect:tags` Command

The `collect:tags` command simply builds the Laravel framework version cache. This is automatically done by the `collect` command; calling the `collect:tags` command directly is not necessary.

## Using the `test:output` Command

The `test:output` command will run all the of the PHPUnit tests for all of the previously generated Illuminate Collection components. This command is useful to check the validity of the Collector utility output. This command __will__ take a while to run if there are a lot of versions to check. In fact, the testing phase is one of the main reasons the split process can be slow.

## Clearing the Caches

At times it may be necessary to clear the caches if you receive error messages related to the various caches the Collector maintains. These caches are located in the following locations (relative to the `collector` installation directory):

| Cache Name | Location | Description |
|---|---|---|
| GitHub Tag Cache | `/storage/cache/github` | A cache of the requests made to the GitHub API. |
| Laravel Framework Version Cache | `/storage/tags/remote.json` | A cache of all the Laravel Framework versions discovered by the Collector utility. |
| Illuminate Component Split History | `/storage/tags/split.json` | A cache of all the versions the Collector utility has previously split. |

All of the cache files can be deleted at any time without any serious side effects. The only one that you probably shouldn't delete that often is the Illuminate Component Split History cache since this is what will help to limit which versions are split in the future.

## License

The Collector utility is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).