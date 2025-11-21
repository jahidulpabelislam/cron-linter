<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests;

use JPI\CronLinter;
use PHPUnit\Framework\TestCase;

final class LinterTest extends TestCase {

    public function testValid(): void {
        $errors = CronLinter::lintContent("
            * * * * * php test.php
            0 * * * * php test.php
            10 * * * * php test.php
            59 * * * * php test.php
            1,2 * * * * php test.php
            * 0 * * * php test.php
            * 2 * * * php test.php
            * 23 * * * php test.php
            * 2,1 * * * php test.php
            * * 1 * * php test.php
            * * 31 * * php test.php
            * * 10,11 * * php test.php
            * * * 1 * php test.php
            * * * 12 * php test.php
            * * * 2,9 * php test.php
            * * * feb * php test.php
            * * * JUN * php test.php
            * * * * 0 php test.php
            * * * * 6 php test.php
            * * * * 5,4 php test.php
            * * * * fri php test.php
            * * * * TuE php test.php
        ");
        $this->assertEquals([], $errors);
    }
}
