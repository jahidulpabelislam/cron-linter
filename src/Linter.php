<?php

declare(strict_types=1);

namespace JPI\CronLinter;

final class Linter
{
    private array $errors = [];

    public static function lintFiles(array $files, string $baseDir = ""): array
    {
        $linter = new static();
        if (empty($files)) {
            return $linter->errors;
        }

        foreach ($files as $filepath) {
            $filepath = $baseDir . "/" . ltrim($filepath, "/");
            if (!file_exists($filepath)) {
                $linter->errors[] = "Missing cron file: $filepath";
                continue;
            }

            $lines = explode("\n", file_get_contents($filepath));
            foreach ($lines as $lineNo => $line) {
                $linter->validateLine($line, $lineNo + 1);
            }
        }

        return $linter->errors;
    }

    public static function lintContent(string $content): array
    {
        $linter = new static();
        if (empty($content)) {
            return $linter->errors;
        }

        $lines = explode("\n", $content);
        foreach ($lines as $lineNo => $line) {
            $linter->validateLine($line, $lineNo + 1);
        }

        return $linter->errors;
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
            "daymonth" => "/^(\d{1,2}|\*)$/",
            "month" => "/^(\*|\d{1,2}|[a-z]{3})$/i",
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
        $validHours = range(0, 23);
        $hours = explode(",", $hours);
        foreach ($hours as $hour) {
            if (!preg_match($regEx["minhour"], $hour) || ($hour != "*" && !in_array($hour, $validHours))) {
                $this->errors[] = "$prefix Hour[$offset]: $hour";
            }
            ++$offset;
        }

        $offset = 0;
        $validDaysOfMonth = range(1, 31);
        $daysOfMonth = explode(",", $daysOfMonth ?? "");
        foreach ($daysOfMonth as $dayOfMonth) {
            if (!preg_match($regEx["daymonth"], $dayOfMonth) || ($dayOfMonth != "*" && !in_array($dayOfMonth, $validDaysOfMonth))) {
                $this->errors[] = "$prefix Day of month[$offset]: $dayOfMonth";
            }
            ++$offset;
        }

        $offset = 0;
        $validMonths = array_merge(
            range(1, 12),
            ["*", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"],
        );
        $months = explode(",", $months ?? "");
        foreach ($months as $month) {
            if (!preg_match($regEx["month"], $month) || !in_array(strtolower($month), $validMonths)) {
                $this->errors[] = "$prefix Month[$offset]: $month";
            }
            ++$offset;
        }

        $offset = 0;
        $validDaysOfWeek = array_merge(
            range(0, 6),
            ["*", "mon", "tue", "wed", "thu", "fri", "sat", "sun"],
        );
        $daysOfWeek = explode(",", $daysOfWeek ?? "");
        foreach ($daysOfWeek as $dayOfWeek) {
            if (!preg_match($regEx["dayweek"], $dayOfWeek) || !in_array(strtolower($dayOfWeek), $validDaysOfWeek)) {
                $this->errors[] = "$prefix Day of week[$offset]: $dayOfWeek";
            }
            ++$offset;
        }

        if (preg_match($regEx["cmdoverflow"], (string) (substr($cmd, 0, 1) == "*"))) {
            $this->errors[] = "Line $lineNo has invalid Cmd: $cmd";
        }
    }
}
