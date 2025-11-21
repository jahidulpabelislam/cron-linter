<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests;

use JPI\CronLinter\Linter;
use PHPUnit\Framework\TestCase;

final class LinterTest extends TestCase {

    public function testAll(): void {
        $linter = new Linter([]);
        $linter->validateLine("* * * * * php test.php", 1);
        $this->assertEmpty($linter->getErrors());
    }
}
