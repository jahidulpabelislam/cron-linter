<?php

declare(strict_types=1);

namespace JPI\CronLinter;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class LintCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('check')
            ->setDescription('Validate Cron files')
            ->addOption(
                'config-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'File to read cronlinter config from in yml format',
                '.cronlinter.yml'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFile = $input->getOption('config-file');

        try {
            $configuration = Yaml::parseFile($configFile);
        } catch (ParseException) {
            $configuration = [];
        }

        $linter = new Linter($configuration['files'] ?? []);

        $errors = $linter->run();

        $output->writeln($errors);

        return empty($errors) ? Command::SUCCESS : Command::FAILURE;
    }
}
