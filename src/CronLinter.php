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
                $valueErrorPrefix = $hasMultipleValues ? "$errorPrefix {$name}[$offset]:" : "$errorPrefix {$name}:";

                $steppedValues = explode("/", $value);
                if (count($steppedValues) > 2) {
                    $this->errors[] = "$valueErrorPrefix $value - too many steps";
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
                        $this->errors[] = "$valueErrorPrefix $firstValue - wildcard * or range supported only";
                        $steppedValues = [$steppedValues[1]];
                    }
                }

                foreach ($steppedValues as $steppedValue) {
                    $rangeValues = array_values(array_filter(explode("-", $steppedValue), fn($value) => $value !== ""));
                    if ($steppedValue[0] === "-")  {
                        $rangeValues[0] = "-" . $rangeValues[0];
                    }
                    if (count($rangeValues) > 2) {
                        $this->errors[] = "$valueErrorPrefix $value";
                        continue;
                    }

                    $hasInvalidValue = false;
                    foreach ($rangeValues as $rangeValue) {
                        if (!preg_match($regEx, $rangeValue) || ($rangeValue !== "*" && !in_array(strtolower($rangeValue), $validValues))) {
                            $this->errors[] = "$valueErrorPrefix $rangeValue";
                            $hasInvalidValue = true;
                        }
                    }

                    // Check if range is ordered (only if both values are valid)
                    if (!$hasInvalidValue && count($rangeValues) === 2) {
                        $start = $rangeValues[0];
                        $end = $rangeValues[1];
                        
                        // Convert to numeric values for comparison
                        $startValue = $this->convertToNumericValue($start);
                        $endValue = $this->convertToNumericValue($end);
                        
                        if ($startValue !== null && $endValue !== null && $startValue > $endValue) {
                            $this->errors[] = "$valueErrorPrefix $steppedValue - range is backwards";
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

    /**
     * Convert a cron value to its numeric representation for comparison.
     * 
     * @param string $value The value to convert (e.g., "10", "jan", "mon")
     * @return int|null The numeric value, or null if conversion fails
     */
    private function convertToNumericValue(string $value): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        // Map month names to their numeric values (jan=1, feb=2, etc.)
        $monthNames = [
            "jan" => 1, "feb" => 2, "mar" => 3, "apr" => 4,
            "may" => 5, "jun" => 6, "jul" => 7, "aug" => 8,
            "sep" => 9, "oct" => 10, "nov" => 11, "dec" => 12,
        ];
        
        // Map day names to their numeric values (mon=1, tue=2, etc., sun=0)
        $dayNames = [
            "mon" => 1, "tue" => 2, "wed" => 3, "thu" => 4,
            "fri" => 5, "sat" => 6, "sun" => 0,
        ];
        
        $lowerValue = strtolower($value);
        
        if (isset($monthNames[$lowerValue])) {
            return $monthNames[$lowerValue];
        }
        
        if (isset($dayNames[$lowerValue])) {
            return $dayNames[$lowerValue];
        }
        
        return null;
    }
}
