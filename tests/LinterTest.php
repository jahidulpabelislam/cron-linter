<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests;

use JPI\CronLinter;
use PHPUnit\Framework\TestCase;

final class LinterTest extends TestCase {

    public function validProvider(): array {
        return [
            ["* * * * * php test.php"],
            ["0 * * * * php test.php"],
            ["10 * * * * php test.php"],
            ["1,2 * * * * php test.php"],
            ["* 0 * * * php test.php"],
            ["* 2 * * * php test.php"],
            ["* 23 * * * php test.php"],
            ["* 2,1 * * * php test.php"],
            ["* * 1 * * php test.php"],
            ["* * 31 * * php test.php"],
            ["* * 10,11 * * php test.php"],
            ["* * * 1 * php test.php"],
            ["* * * 12 * php test.php"],
            ["* * * 2,9 * php test.php"],
            ["* * * feb * php test.php"],
            ["* * * JUN * php test.php"],
            ["* * * * 0 php test.php"],
            ["* * * * 6 php test.php"],
            ["* * * * 5,4 php test.php"],
            ["* * * * fri php test.php"],
            ["* * * * TuE php test.php"],
        ];
    }

    /**
     * @dataProvider validProvider
     * @return void
     */
    public function testValid(string $expression): void {
        $errors = CronLinter::lintContent($expression);
        $this->assertCount(0, $errors);
    }

    public function invalidProvider(): array {
        return [
            ["-0 * * * * php test.php", "Line 1 has invalid value for Minute[0]: -0"],
            ["60 * * * * php test.php", "Line 1 has invalid value for Minute[0]: 60"],
            ["* -1 * * * php test.php", "Line 1 has invalid value for Hour[0]: -1"],
            ["* 24 * * * php test.php", "Line 1 has invalid value for Hour[0]: 24"],
            ["* * -2 * * php test.php", "Line 1 has invalid value for Day of month[0]: -2"],
            ["* * 32 * * php test.php", "Line 1 has invalid value for Day of month[0]: 32"],
            ["* * * -3 * php test.php", "Line 1 has invalid value for Month[0]: -3"],
            ["* * * 13 * php test.php", "Line 1 has invalid value for Month[0]: 13"],
            ["* * * june * php test.php", "Line 1 has invalid value for Month[0]: june"],
            ["* * * * -4 php test.php", "Line 1 has invalid value for Day of week[0]: -4"],
            ["* * * * 7 php test.php", "Line 1 has invalid value for Day of week[0]: 7"],
            ["* * * * thurs php test.php", "Line 1 has invalid value for Day of week[0]: thurs"],
        ];
    }

    /**
     * @dataProvider invalidProvider
     * @return void
     */
    public function testInvalid(string $expression, string $expectedError): void {
        $errors = CronLinter::lintContent($expression);
        $this->assertCount(1, $errors);
        $this->assertEquals($expectedError, $errors[0]);
    }
}
