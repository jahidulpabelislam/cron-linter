<?php

declare(strict_types=1);

namespace JPI\CronLinter;

final class Linter
{
    private array $errors = [];

    public function __construct(private array $files, private string $baseDir = "")
    {
    }

    public function __invoke(): array
    {
        if (empty($this->files)) {
            return $this->errors;
        }

        foreach ($this->files as $filepath) {
            $filepath = $this->baseDir . "/" . ltrim($filepath, "/");
            if (!file_exists($filepath)) {
                $this->errors[] = "Missing cron file: $filepath";
                continue;
            }

            $lines = explode("\n", file_get_contents($filepath));
            foreach ($lines as $lineNo => $line) {
                $this->validateLine($line, $lineNo + 1);
            }
        }

        return $this->errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validateLine(string $line, int $lineNo): void
    {
        // Skip comment lines or empty lines
        if (empty($line) || str_starts_with($line, "#")) {
            return;
        }

        $prefix = "Line $lineNo has invalid value for";

        $line = str_replace("\t", " ", $line);
        $args = array_values(
            array_filter(
                explode(" ", $line),
                function ($v) {
                    return (bool) strlen($v);
                }
            )
        );

        if (count($args) < 6) {
            $this->errors[] = "Line $lineNo has missing time expression";
            return;
        }

        $cmd = implode(" ", array_slice($args, 5));
        list($mins, $hours, $daysOfMonth, $months, $daysOfWeek) = array_slice($args, 0, 5);

        $regEx = [
            "minhour" => "/^([\*|\d]+)$|^([\*|\d]+?(\-\d+))$|^([\*]\/\d+)$|^([\d+]\/\d+?(\-\d+))$|^(\d+-\d+\/[\d]+)$/i",
            "daymonth" => "/^(\d{1,2}|\*)$/i",
            "month" => "/^(\d{1,2}|\*)$/i",
            "dayweek" => "/^(\*|\d|[a-z]{3})$/i",
            "cmdoverflow" => "/^(\d|\*)$/i",
        ];

        $offset = 0;
        $mins = explode(",", $mins);
        foreach ($mins as $min) {
            if (!preg_match($regEx["minhour"], $min)) {
                $this->errors[] = "$prefix Minute[$offset]: $min";
            }
            ++$offset;
        }

        $offset = 0;
        $hours = explode(",", $hours);
        foreach ($hours as $hour) {
            if (!preg_match($regEx["minhour"], $hour)) {
                $this->errors[] = "$prefix Hour[$offset]: $hour";
            }
            ++$offset;
        }

        $offset = 0;
        $daysOfMonth = explode(",", $daysOfMonth ?? "");
        foreach ($daysOfMonth as $dayOfMonth) {
            if (!preg_match($regEx["daymonth"], $dayOfMonth)) {
                $this->errors[] = "$prefix Day of month[$offset]: $dayOfMonth";
            }
            ++$offset;
        }

        $offset = 0;
        $daysOfWeek = explode(",", $daysOfWeek ?? "");
        foreach ($daysOfWeek as $dayOfWeek) {
            if (!preg_match($regEx["dayweek"], $dayOfWeek)) {
                $this->errors[] = "$prefix Day of week[$offset]: $dayOfWeek";
            }
            ++$offset;
        }

        if (preg_match($regEx["cmdoverflow"], (string) (substr($cmd, 0, 1) == "*"))) {
            $this->errors[] = "Line $lineNo has invalid Cmd: $cmd";
        }
    }
}
