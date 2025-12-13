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
            if ($baseDir !== "") {
                $filepath = rtrim($baseDir, "/") . "/" . ltrim($filepath, "/");
            }

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

        $checks = [
            "Minute" => [
                "values" => $args[0],
                "options" => range(0, 59),
            ],
            "Hour" => [
                "values" => $args[1],
                "options" => range(0, 23),
            ],
            "Day of month" => [
                "values" => $args[2],
                "options" => range(1, 31),
            ],
            "Month" => [
                "regex" => "/^(\*|\d{1,2}|[a-z]{3})$/i",
                "values" => $args[3],
                "options" => array_merge(
                    range(1, 12),
                    ["*", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"],
                ),
            ],
            "Day of week" => [
                "regex" => "/^(\*|\d|[a-z]{3})$/i",
                "values" => $args[4],
                "options" => array_merge(
                    range(0, 6),
                    ["*", "mon", "tue", "wed", "thu", "fri", "sat", "sun"],
                ),
            ],
        ];

        $defaultRegex = "/^(\d{1,2}|\*)$/";
        $errorPrefix = "Line $lineNo has invalid value for";

        foreach ($checks as $name => $data) {
            $offset = 0;
            $regEx = $data["regex"] ?? $defaultRegex;
            $validValues = $data["options"];
            $values = explode(",", $data["values"]);
            $hasMultipleValues = count($values) > 1;
            foreach ($values as $value) {
                $valueErrorPrefix = $hasMultipleValues ? "$errorPrefix {$name}[$offset]:" : "$errorPrefix $name:";

                $steppedValues = explode("/", $value);
                if (count($steppedValues) > 2) {
                    $stepsErrorName = $hasMultipleValues ? "{$name}[$offset]" : $name;
                    $this->errors[] = "Line $lineNo has too many step values for $stepsErrorName: $value";
                    continue;
                }

                // If stepped, the first value has to be a range or *
                if (count($steppedValues) === 2 && $steppedValues[0] !== "*") {
                    $firstValue = $steppedValues[0];
                    $rangeValues = array_values(array_filter(explode("-", $firstValue), fn($value) => $value !== ""));
                    if ($firstValue[0] === "-")  {
                        $rangeValues[0] = "-" . $rangeValues[0];
                    }
                    if (count($rangeValues) !== 2) {
                        $this->errors[] = "$valueErrorPrefix $firstValue (only wildcard * or range supported)";
                        $steppedValues = [$steppedValues[1]];
                    }
                }

                foreach ($steppedValues as $steppedValue) {
                    $rangeValues = array_values(array_filter(explode("-", $steppedValue), fn($value) => $value !== ""));
                    if ($steppedValue[0] === "-")  {
                        $rangeValues[0] = "-" . $rangeValues[0];
                    }
                    if (count($rangeValues) > 2) {
                        $rangeErrorName = $hasMultipleValues ? "{$name}[$offset]" : $name;
                        $this->errors[] = "Line $lineNo has too many values in the range for $rangeErrorName: $value";
                        continue;
                    }

                    foreach ($rangeValues as $rangeValue) {
                        if (!preg_match($regEx, $rangeValue) || ($rangeValue !== "*" && !in_array(strtolower($rangeValue), $validValues))) {
                            $this->errors[] = "$valueErrorPrefix $rangeValue";
                        }
                    }
                }

                ++$offset;
            }
        }

        $cmd = implode(" ", array_slice($args, 5));
        if (preg_match("/^(\d|\*)$/i", (string) (substr($cmd, 0, 1) == "*"))) {
            $this->errors[] = "Line $lineNo has invalid Cmd: $cmd";
        }
    }
}
