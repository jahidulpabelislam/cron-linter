<?php

declare(strict_types=1);

namespace JPI\CronLinter;

final class Linter
{
    private array $errors = [];

    public function __construct(private array $files, private string $baseDir = '')
    {
    }

    public function __invoke(): array
    {
        if (empty($this->files)) {
            return $this->errors;
        }

        foreach ($this->files as $filepath) {
            $filepath = $this->baseDir . "/" . ltrim($filepath, '/');
            if (!file_exists($filepath)) {
                $this->errors[] = 'Missing cron file: ' . $filepath;
                continue;
            }

            $lines = explode("\n", file_get_contents($filepath));
            foreach ($lines as $lineNo => $line) {
                $this->validateLine($line, $lineNo + 1);
            }
        }

        return $this->errors;
    }

    protected function validateLine(string $line, int $lineNo): void
    {
        // Skip comment lines or empty lines
        if (empty($line) || str_starts_with($line, '#')) {
            return;
        }

        $prefix = sprintf("Invalid value on line %d for", $lineNo);

        $line = str_replace("\t", " ", $line);
        $args = array_values(
            array_filter(
                explode(" ", $line),
                function ($v) {
                    return (bool) strlen($v);
                }
            )
        );
        $cmd = implode(' ', array_slice($args, 5));
        list($mins, $hours, $dayOfMonth, $month, $dayOfWeek) = array_slice($args, 0, 5);

        $regEx = [
            'minhour' => '/^([\*|\d]+)$|^([\*|\d]+?(\-\d+))$|^([\*]\/\d+)$|^([\d+]\/\d+?(\-\d+))$|^(\d+-\d+\/[\d]+)$/i',
            'daymonth' => '/^(\d{1,2}|\*)$/i',
            'month' => '/^(\d{1,2}|\*)$/i',
            'dayweek' => '/^(\*|\d|[a-z]{3})$/i',
            'cmdoverflow' => '/^(\d|\*)$/i',
        ];

        $offset = 0;
        $mins = explode(',', $mins);
        foreach ($mins as $min) {
            if (!preg_match($regEx['minhour'], $min)) {
                $this->errors[] = sprintf("$prefix Minute[%d]: %s", $offset, $min);
            }
            ++$offset;
        }

        $offset = 0;
        $hours = explode(',', $hours);
        foreach ($hours as $hour) {
            if (!preg_match($regEx['minhour'], $hour)) {
                $this->errors[] = sprintf("$prefix Hour[%d]: %s", $offset, $hour);
            }
            ++$offset;
        }

        $offset = 0;
        $dayOfMonth = explode(',', $dayOfMonth);
        foreach ($dayOfMonth as $dom) {
            if (!preg_match($regEx['daymonth'], $dom)) {
                $this->errors[] = sprintf("$prefix Day of month[%d]: %s", $offset, $dom);
            }
            ++$offset;
        }

        $offset = 0;
        $dayOfWeek = explode(',', $dayOfWeek);
        foreach ($dayOfWeek as $dow) {
            if (!preg_match($regEx['dayweek'], $dow)) {
                $this->errors[] = sprintf("$prefix Day of week[%d]: %s", $offset, $dow);
            }
            ++$offset;
        }

        if (preg_match($regEx['cmdoverflow'], (string) (substr($cmd, 0, 1) == '*'))) {
            $this->errors[] = sprintf("Line %d has invalid Cmd: %s", $lineNo, $cmd);
        }
    }
}
