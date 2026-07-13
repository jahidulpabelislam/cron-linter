<?php

declare(strict_types=1);

namespace JPI\CronLinter\Tests\Unit;

use JPI\CronLinter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JPI\CronLinter:lintFiles
 * @covers \JPI\CronLinter:lintFile
 */
final class FilesTest extends TestCase {

    public function testWithGlob(): void {
        $linter = CronLinter::lintFiles([__DIR__ . "/../fixtures/valid/cron.*"]);
        $this->assertCount(0, $linter->getErrors());
    }

    public function testWithDirectoryGlob(): void {
        $linter = CronLinter::lintFiles([__DIR__ . "/../fixtures/valid/cron.d/*"]);
        $this->assertCount(0, $linter->getErrors());
    }

    public function testWithGlobAndBaseDir(): void {
        $linter = CronLinter::lintFiles(["/cron.*"], __DIR__ . "/../fixtures/valid");
        $this->assertCount(0, $linter->getErrors());
    }

    public function testWithGlobNoMatches(): void {
        $linter = CronLinter::lintFiles([__DIR__ . "/../fixtures/valid/nonexistent.*"]);
        $this->assertCount(0, $linter->getErrors());
    }

    public function testWithGlobAndInvalidContent(): void {
        $errors = CronLinter::lintFiles([__DIR__ . "/../fixtures/invalid/cron.*"])->getErrors();
        $this->assertCount(1, $errors);
        $expectedFile = __DIR__ . "/../fixtures/invalid/cron.daily";
        $this->assertArrayHasKey($expectedFile, $errors);
        $this->assertSame(["Line 1 has missing time expression"], $errors[$expectedFile]);
    }
}
