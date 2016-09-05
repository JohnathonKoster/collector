![](https://cloud.githubusercontent.com/assets/5232890/18241574/88bbf030-7317-11e6-90f2-92af52c626e9.png)

The `collector` tool attempts to automate the process of splitting the `Illuminate\Support\Collection` component from the Laravel code-base. It strives to solve this issue: https://github.com/tightenco/collect/issues/2. The output of this utility can be viewed at https://github.com/JohnathonKoster/collector-output-test (it is not recommended to use the releases within the `collector-output-test` repository directly; instead, use Tighten Co's repository).

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