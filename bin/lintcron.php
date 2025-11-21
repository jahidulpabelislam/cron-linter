#!/usr/bin/env php
<?php

declare(strict_types=1);

use JPI\CronLinter\Command;
use Symfony\Component\Console\Application;

$paths = [
    "local" => __DIR__ . "/../vendor/autoload.php",
    "dependency" => __DIR__ . "/../../../autoload.php"
];

$isDependency = false;
$projectRoot = __DIR__ . "/..";
$loaded = false;

foreach ($paths as $type => $path) {
    if (file_exists($path)) {
        if ($type === "dependency") {
            $projectRoot = __DIR__ . "/../../../..";
            $isDependency = true;
        }
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    echo "Unable to load composer autoloader";
    exit(1);
}

(new Application("Cron linter", "@package_version@"))
    ->add(new Command())
    ->getApplication()
    ->setDefaultCommand("check", true)
    ->run();
