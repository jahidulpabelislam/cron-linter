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
    /**
     * Configure the command options and description.
     */
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
                "Comma separated list of cron files to check"
            )
        ;
    }

    /**
     * Execute the cron linting command.
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     * @return int The exit code (SUCCESS or FAILURE)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseDir = getcwd();

        $files = $input->getOption("files");
        if (!$files) {
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
            $files = explode(",", $files);
        }

        $errors = CronLinter::lintFiles($files, $baseDir);

        $output->writeln($errors);

        return empty($errors) ? self::SUCCESS : self::FAILURE;
    }
}
