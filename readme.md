![](https://cloud.githubusercontent.com/assets/5232890/18241574/88bbf030-7317-11e6-90f2-92af52c626e9.png)

The `collector` tool attempts to automate the process of splitting the `Illuminate\Support\Collection` component from the Laravel code-base. It strives to solve this issue: https://github.com/tightenco/collect/issues/2. The output of this utility can be viewed at https://github.com/JohnathonKoster/collector-output-test (it is not recommended to use the releases within the `collector-output-test` repository directly; instead, use Tighten Co's repository).

## Download and Installation

The easiest way to download the Collector utility is just to clone the repository:

```
git clone https://github.com/JohnathonKoster/collector.git
```

After you have obtained the source, you must install the Composer dependencies using `composer install`. Composer will install things such as the Symfony console and process components, a PHP Parser, a GitHub API and some filesystem testing utilities.

## Configuration

