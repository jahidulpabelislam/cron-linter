# Cron Linter

[![CodeFactor](https://www.codefactor.io/repository/github/jahidulpabelislam/cron-linter/badge)](https://www.codefactor.io/repository/github/jahidulpabelislam/cron-linter)
[![Latest Stable Version](https://poser.pugx.org/jpi/cron-linter/v/stable)](https://packagist.org/packages/jpi/cron-linter)
[![Total Downloads](https://poser.pugx.org/jpi/cron-linter/downloads)](https://packagist.org/packages/jpi/cron-linter)
[![Latest Unstable Version](https://poser.pugx.org/jpi/cron-linter/v/unstable)](https://packagist.org/packages/jpi/cron-linter)
[![License](https://poser.pugx.org/jpi/cron-linter/license)](https://packagist.org/packages/jpi/cron-linter)
![GitHub last commit (branch)](https://img.shields.io/github/last-commit/jahidulpabelislam/cron-linter/1.x.svg?label=last%20activity)

Lint cron files. Based on the work of @Dave13h from https://github.com/Dave13h/php-censor-cronlint-plugin, just the PHPCensor aspect removed and made into a standalone package.

# Installation

```composer require --dev jpi/cron-linter```

Add `.cronlinter.yml` file to your project root, that provides a list of files to be linted.

```yml
files:
  - /cron-1
  - /cron-2
```
Then run `php vendor/bin/lintcron`, it should then list any errors found in the specified cron files.

Or if you want to do it programmatically on files or content.

```php
// $files is an array of file paths for cron's to check, $baseDir (optional) is a string containing the base directory path for all paths
$errors = \JPI\CronLinter::lintFiles($files, $baseDir);

// $expression is a string containing the cron expression(s)
$errors = \JPI\CronLinter::lintContent($expression);

// Both return an array of errors found, empty array if no errors found.
```

## Support

If you found this library interesting or useful please spread the word about this library: share on your socials, star on GitHub, etc.

If you find any issues or have any feature requests, you can open a [issue](https://github.com/jahidulpabelislam/cron-linter/issues) or email [me @ jahidulpabelislam.com](mailto:me@jahidulpabelislam.com) :smirk:.

## Authors

-   [Jahidul Pabel Islam](https://jahidulpabelislam.com/) [<me@jahidulpabelislam.com>](mailto:me@jahidulpabelislam.com)

## Licence

This module is licenced under the General Public Licence - see the [licence](LICENSE.md) file for details
