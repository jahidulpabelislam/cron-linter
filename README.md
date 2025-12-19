# Cron Linter

[![CodeFactor](https://www.codefactor.io/repository/github/jahidulpabelislam/cron-linter/badge)](https://www.codefactor.io/repository/github/jahidulpabelislam/cron-linter)
[![Latest Stable Version](https://poser.pugx.org/jpi/cron-linter/v/stable)](https://packagist.org/packages/jpi/cron-linter)
[![Total Downloads](https://poser.pugx.org/jpi/cron-linter/downloads)](https://packagist.org/packages/jpi/cron-linter)
[![Latest Unstable Version](https://poser.pugx.org/jpi/cron-linter/v/unstable)](https://packagist.org/packages/jpi/cron-linter)
[![License](https://poser.pugx.org/jpi/cron-linter/license)](https://packagist.org/packages/jpi/cron-linter)
![GitHub last commit (branch)](https://img.shields.io/github/last-commit/jahidulpabelislam/cron-linter/1.x.svg?label=last%20activity)

Lint cron files. Based on the work of @Dave13h from https://github.com/Dave13h/php-censor-cronlint-plugin, just the PHPCensor aspect removed and made into a standalone package.

# Installation

```bash
composer require --dev jpi/cron-linter
```

Add a `.cronlinter.yml` file to your project root that specifies a list of cron files to lint.

```yml
files:
  - /cron-1
  - /cron-2
```

Then run `php vendor/bin/lintcron`, which will list any errors found in the specified cron files. You can use a different config file location using the `--config-file` option.

You can also specify files to be linted using the `--files` option, providing a comma-separated list of file paths.

Or if you want to do it programmatically on files or content.

```php
// Lint cron files
// $files is an array of file paths for cron files to check
// $baseDir (optional) is a string containing the base directory path for all file paths
$errors = \JPI\CronLinter::lintFiles($files, $baseDir);

// Lint cron content directly
// $expression is a string containing one or more cron expressions (one per line)
$errors = \JPI\CronLinter::lintContent($expression);

// Both methods return an array of error messages, or an empty array if no errors found
```

## Support

If you found this library interesting or useful please spread the word about this library: share on your socials, star on GitHub, etc.

If you find any issues or have any feature requests, you can open a [issue](https://github.com/jahidulpabelislam/cron-linter/issues) or email [me @ jahidulpabelislam.com](mailto:me@jahidulpabelislam.com) :smirk:.

## Authors

-   [Jahidul Pabel Islam](https://jahidulpabelislam.com/) [<me@jahidulpabelislam.com>](mailto:me@jahidulpabelislam.com)

## License

This module is licensed under the GNU General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details
