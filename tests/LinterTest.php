<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests;

use JPI\CronLinter\Linter;
use PHPUnit\Framework\TestCase;

final class LinterTest extends TestCase {

    public function testAll(): void {
        $errors = Linter::lintContent(
            "* * * * * php test.php"
        );
        $this->assertEmpty($errors);
    }
}
