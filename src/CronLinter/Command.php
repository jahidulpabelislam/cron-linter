<?php

declare(strict_types=1);

namespace JPI\CronLinter;

use JPI\CronLinter;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Command extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName("check")
            ->setDescription("Validate Cron files")
            ->addOption(
                "config-file",
                null,
                InputOption::VALUE_OPTIONAL,
                "File to read cronlinter config from in yml format"
            )
            ->addOption(
                "files",
                null,
                InputOption::VALUE_OPTIONAL,
                "Comma separated list of cron files to check",
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseDir = getcwd();

        if ($input->getOption("files") === false) {
            $configFile = $input->getOption("config-file");

            if (!$configFile) {
                $configFile = "$baseDir/.cronlinter.yml";
            }

            try {
                $configuration = Yaml::parseFile($configFile);
            } catch (ParseException) {
                $configuration = [];
            }

            $files = $configuration["files"] ?? [];
        } else {
            $files = explode(",", $input->getOption("files") ?: "");
        }

        $files = array_values(array_filter(array_map(
            static fn (mixed $file): string => trim((string) $file),
            (array) $files
        )));

        $linter = CronLinter::lintFiles($files, $baseDir);

        $output->writeln("Checked " . $linter->getNumberOfFilesChecked() . " cron files" . PHP_EOL);

        $groupedErrors = $linter->getErrors();

        if (!empty($groupedErrors)) {
            $isFirst = true;
            foreach ($groupedErrors as $file => $errors) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $output->writeln(PHP_EOL);
                }
                $output->writeln($file . ":");
                foreach ($errors as $error) {
                    $output->writeln("    " . $error);
                }
            }
        } else {
            $output->writeln(!empty($files) ? "Cron files all valid" : "No cron files available to check");
        }

        return empty($groupedErrors) ? self::SUCCESS : self::FAILURE;
    }
}
