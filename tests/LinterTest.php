<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests;

use JPI\CronLinter;
use PHPUnit\Framework\TestCase;

final class LinterTest extends TestCase {

    public function testAll(): void {
        $errors = CronLinter::lintContent(
            "* * * * * php test.php"
        );
        $this->assertEmpty($errors);
    }
}
