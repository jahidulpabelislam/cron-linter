<?php

declare(strict_types=1);

namespace JPI\CronLinter;

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
                "File to read cronlinter config from in yml format",
                ".cronlinter.yml"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $configuration = Yaml::parseFile($input->getOption("config-file"));
        } catch (ParseException) {
            $configuration = [];
        }

        $errors = Linter::fromFiles($configuration["files"] ?? [], getcwd());

        $output->writeln($errors);

        return empty($errors) ? self::SUCCESS : self::FAILURE;
    }
}
