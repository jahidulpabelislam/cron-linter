<?php

declare(strict_types=1);

namespace JPI;

final class CronLinter
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
        $content = trim($content, "\n ");
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
        list($minutes, $hours, $daysOfMonth, $months, $daysOfWeek) = array_slice($args, 0, 5);

        $checks = [
            "Minute" => [
                "values" => $minutes,
                "options" => range(0, 59),
            ],
            "Hour" => [
                "values" => $hours,
                "options" => range(0, 23),
            ],
            "Day of month" => [
                "values" => $daysOfMonth,
                "options" => range(1, 31),
            ],
            "Month" => [
                "regex" => "/^(\*|\d{1,2}|[a-z]{3})$/i",
                "values" => $months,
                "options" => array_merge(
                    range(1, 12),
                    ["*", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"],
                ),
            ],
            "Day of week" => [
                "regex" => "/^(\*|\d|[a-z]{3})$/i",
                "values" => $daysOfWeek,
                "options" => array_merge(
                    range(0, 6),
                    ["*", "mon", "tue", "wed", "thu", "fri", "sat", "sun"],
                ),
            ],
        ];

        $defaultRegex = "/^(\d{1,2}|\*)$/";

        foreach ($checks as $name => $data) {
            $offset = 0;
            $regEx = $data["regex"] ?? $defaultRegex;
            $validValues = $data["options"];
            $values = explode(",", $data["values"]);
            foreach ($values as $value) {
                if (!preg_match($regEx, $value) || ($value != "*" && !in_array($value, $validValues))) {
                    $this->errors[] = "$prefix {$name}[$offset]: $value";
                }
                ++$offset;
            }
        }

        if (preg_match("/^(\d|\*)$/i", (string) (substr($cmd, 0, 1) == "*"))) {
            $this->errors[] = "Line $lineNo has invalid Cmd: $cmd";
        }
    }
}
