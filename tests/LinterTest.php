<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests;

use JPI\CronLinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LinterTest extends TestCase {

    public static function validProvider(): array {
        return [
            ["* * * * * php test.php"],

            ["0 * * * * php test.php"],
            ["10 * * * * php test.php"],
            ["1,2 * * * * php test.php"],
            ["15,45 * * * * php test.php"],
            ["15-45 * * * * php test.php"],
            ["*/2 * * * * php test.php"],
            ["1-59/2 * * * * php test.php"],

            ["* 0 * * * php test.php"],
            ["* 2 * * * php test.php"],
            ["* 23 * * * php test.php"],
            ["* 6,12 * * * php test.php"],
            ["* 0-6 * * * php test.php"],
            ["* */3 * * * php test.php"],
            ["* 0-12/6 * * * php test.php"],

            ["* * 1 * * php test.php"],
            ["* * 31 * * php test.php"],
            ["* * 10,11 * * php test.php"],
            ["* * 7-21 * * php test.php"],
            ["* * */5 * * php test.php"],
            ["* * 21-31/2 * * php test.php"],

            ["* * * 1 * php test.php"],
            ["* * * 12 * php test.php"],
            ["* * * 2,9 * php test.php"],
            ["* * * 6-9 * php test.php"],
            ["* * * */2 * php test.php"],
            ["* * * 6-12/2 * php test.php"],
            ["* * * feb * php test.php"],
            ["* * * JUN * php test.php"],

            ["* * * * 0 php test.php"],
            ["* * * * 6 php test.php"],
            ["* * * * 5,4 php test.php"],
            ["* * * * 2-4 php test.php"],
            ["* * * * */3 php test.php"],
            ["* * * * 1-5/4 php test.php"],
            ["* * * * fri php test.php"],
            ["* * * * TuE php test.php"],
        ];
    }

    #[DataProvider("validProvider")]
    public function testValid(string $expression): void {
        $errors = CronLinter::lintContent($expression);
        $this->assertCount(0, $errors);
    }

    public static function invalidProvider(): array {
        return [
            ["-0 * * * * php test.php", ["Line 1 has invalid value for Minute: -0"]],
            ["-0-10 * * * * php test.php", ["Line 1 has invalid value for Minute: -0"]],
            ["60 * * * * php test.php", ["Line 1 has invalid value for Minute: 60"]],
            ["59,69 * * * * php test.php", ["Line 1 has invalid value for Minute[1]: 69"]],
            ["60,65 * * * * php test.php", ["Line 1 has invalid value for Minute[0]: 60", "Line 1 has invalid value for Minute[1]: 65"]],
            ["*/60 * * * * php test.php", ["Line 1 has invalid value for Minute: 60"]],
            ["55-61/2 * * * * php test.php", ["Line 1 has invalid value for Minute: 61"]],
            ["61-62/2 * * * * php test.php", ["Line 1 has invalid value for Minute: 61", "Line 1 has invalid value for Minute: 62"]],
            ["1/2 * * * * php test.php", ["Line 1 has invalid value for Minute: 1 - wildcard * or range supported only"]],
            ["*/1/2 * * * * php test.php", ["Line 1 has invalid value for Minute: */1/2 - too many steps"]],
            ["10-9 * * * * php test.php", ["Line 1 has invalid value for Minute: 10-9 - range is backwards"]],
            ["59-0 * * * * php test.php", ["Line 1 has invalid value for Minute: 59-0 - range is backwards"]],
            ["61-59 * * * * php test.php", ["Line 1 has invalid value for Minute: 61"]],
            ["10-9/2 * * * * php test.php", ["Line 1 has invalid value for Minute: 10-9 - range is backwards"]],

            ["* -1 * * * php test.php", ["Line 1 has invalid value for Hour: -1"]],
            ["* -1-23 * * * php test.php", ["Line 1 has invalid value for Hour: -1"]],
            ["* 24 * * * php test.php", ["Line 1 has invalid value for Hour: 24"]],
            ["* 12,24 * * * php test.php", ["Line 1 has invalid value for Hour[1]: 24"]],
            ["* 24,32 * * * php test.php", ["Line 1 has invalid value for Hour[0]: 24", "Line 1 has invalid value for Hour[1]: 32"]],
            ["* */40 * * * php test.php", ["Line 1 has invalid value for Hour: 40"]],
            ["* 23-24/2 * * * php test.php", ["Line 1 has invalid value for Hour: 24"]],
            ["* 24-26/2 * * * php test.php", ["Line 1 has invalid value for Hour: 24","Line 1 has invalid value for Hour: 26"]],
            ["* 1/2 * * * php test.php", ["Line 1 has invalid value for Hour: 1 - wildcard * or range supported only"]],
            ["* 1/2/3 * * * php test.php", ["Line 1 has invalid value for Hour: 1/2/3 - too many steps"]],
            ["* 23-0 * * * php test.php", ["Line 1 has invalid value for Hour: 23-0 - range is backwards"]],
            ["* 12-5 * * * php test.php", ["Line 1 has invalid value for Hour: 12-5 - range is backwards"]],

            ["* * -2 * * php test.php", ["Line 1 has invalid value for Day of month: -2"]],
            ["* * -2-14 * * php test.php", ["Line 1 has invalid value for Day of month: -2"]],
            ["* * 0 * * php test.php", ["Line 1 has invalid value for Day of month: 0"]],
            ["* * 32 * * php test.php", ["Line 1 has invalid value for Day of month: 32"]],
            ["* * 28,35 * * php test.php", ["Line 1 has invalid value for Day of month[1]: 35"]],
            ["* * 32,35 * * php test.php", ["Line 1 has invalid value for Day of month[0]: 32", "Line 1 has invalid value for Day of month[1]: 35"]],
            ["* * */32 * * php test.php", ["Line 1 has invalid value for Day of month: 32"]],
            ["* * 21-35/2 * * php test.php", ["Line 1 has invalid value for Day of month: 35"]],
            ["* * 32-35/2 * * php test.php", ["Line 1 has invalid value for Day of month: 32", "Line 1 has invalid value for Day of month: 35"]],
            ["* * 7/14 * * php test.php", ["Line 1 has invalid value for Day of month: 7 - wildcard * or range supported only"]],
            ["* * 7/14/28 * * php test.php", ["Line 1 has invalid value for Day of month: 7/14/28 - too many steps"]],
            ["* * 31-1 * * php test.php", ["Line 1 has invalid value for Day of month: 31-1 - range is backwards"]],
            ["* * 15-10 * * php test.php", ["Line 1 has invalid value for Day of month: 15-10 - range is backwards"]],

            ["* * * -3 * php test.php", ["Line 1 has invalid value for Month: -3"]],
            ["* * * -3-12 * php test.php", ["Line 1 has invalid value for Month: -3"]],
            ["* * * 0 * php test.php", ["Line 1 has invalid value for Month: 0"]],
            ["* * * 13 * php test.php", ["Line 1 has invalid value for Month: 13"]],
            ["* * * 3,13 * php test.php", ["Line 1 has invalid value for Month[1]: 13"]],
            ["* * * 13,14 * php test.php", ["Line 1 has invalid value for Month[0]: 13", "Line 1 has invalid value for Month[1]: 14"]],
            ["* * * */24 * php test.php", ["Line 1 has invalid value for Month: 24"]],
            ["* * * 8-16/2 * php test.php", ["Line 1 has invalid value for Month: 16"]],
            ["* * * 14-16/2 * php test.php", ["Line 1 has invalid value for Month: 14", "Line 1 has invalid value for Month: 16"]],
            ["* * * 8/12 * php test.php", ["Line 1 has invalid value for Month: 8 - wildcard * or range supported only"]],
            ["* * * 8/12/16 * php test.php", ["Line 1 has invalid value for Month: 8/12/16 - too many steps"]],
            ["* * * june * php test.php", ["Line 1 has invalid value for Month: june"]],
            ["* * * 12-1 * php test.php", ["Line 1 has invalid value for Month: 12-1 - range is backwards"]],
            ["* * * dec-jan * php test.php", ["Line 1 has invalid value for Month: dec-jan - range is backwards"]],
            ["* * * mar-feb * php test.php", ["Line 1 has invalid value for Month: mar-feb - range is backwards"]],

            ["* * * * -4 php test.php", ["Line 1 has invalid value for Day of week: -4"]],
            ["* * * * -3-5 php test.php", ["Line 1 has invalid value for Day of week: -3"]],
            ["* * * * 7 php test.php", ["Line 1 has invalid value for Day of week: 7"]],
            ["* * * * 4,8 php test.php", ["Line 1 has invalid value for Day of week[1]: 8"]],
            ["* * * * 7,8 php test.php", ["Line 1 has invalid value for Day of week[0]: 7", "Line 1 has invalid value for Day of week[1]: 8"]],
            ["* * * * */7 php test.php", ["Line 1 has invalid value for Day of week: 7"]],
            ["* * * * 4-8/2 php test.php", ["Line 1 has invalid value for Day of week: 8"]],
            ["* * * * 8-10/2 php test.php", ["Line 1 has invalid value for Day of week: 8", "Line 1 has invalid value for Day of week: 10"]],
            ["* * * * 8/2 php test.php", ["Line 1 has invalid value for Day of week: 8 - wildcard * or range supported only"]],
            ["* * * * 3/6/9 php test.php", ["Line 1 has invalid value for Day of week: 3/6/9 - too many steps"]],
            ["* * * * thurs php test.php", ["Line 1 has invalid value for Day of week: thurs"]],
            ["* * * * 6-0 php test.php", ["Line 1 has invalid value for Day of week: 6-0 - range is backwards"]],
            ["* * * * 5-2 php test.php", ["Line 1 has invalid value for Day of week: 5-2 - range is backwards"]],
            ["* * * * fri-mon php test.php", ["Line 1 has invalid value for Day of week: fri-mon - range is backwards"]],
        ];
    }

    #[DataProvider("invalidProvider")]
    public function testInvalid(string $expression, array $expectedErrors): void {
        $errors = CronLinter::lintContent($expression);
        $this->assertCount(count($expectedErrors), $errors);
        $this->assertEquals($expectedErrors, $errors);
    }
}
