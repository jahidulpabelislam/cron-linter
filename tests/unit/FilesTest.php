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
        $this->assertSame(2, $linter->getNumberOfFilesChecked());
    }

    public function testWithDirectoryGlob(): void {
        $linter = CronLinter::lintFiles([__DIR__ . "/../fixtures/valid/cron.d/*"]);
        $this->assertCount(0, $linter->getErrors());
        $this->assertSame(2, $linter->getNumberOfFilesChecked());
    }

    public function testWithGlobAndBaseDir(): void {
        $linter = CronLinter::lintFiles(["/cron.*"], __DIR__ . "/../fixtures/valid");
        $this->assertCount(0, $linter->getErrors());
        $this->assertSame(2, $linter->getNumberOfFilesChecked());
    }

    public function testWithGlobNoMatches(): void {
        $linter = CronLinter::lintFiles([__DIR__ . "/../fixtures/valid/nonexistent.*"]);
        $errors = $linter->getErrors();
        $this->assertCount(1, $errors);
        $filepath = __DIR__ . "/../fixtures/valid/nonexistent.*";
        $this->assertArrayHasKey($filepath, $errors);
        $this->assertSame(["No matching cron files found"], $errors[$filepath]);
        $this->assertSame(0, $linter->getNumberOfFilesChecked());
    }

    public function testWithGlobAndInvalidContent(): void {
        $linter = CronLinter::lintFiles([__DIR__ . "/../fixtures/invalid/cron.*"]);
        $errors = $linter->getErrors();
        $this->assertCount(1, $errors);
        $filepath = __DIR__ . "/../fixtures/invalid/cron.daily";
        $this->assertArrayHasKey($filepath, $errors);
        $this->assertSame(["Line 1 has missing time expression"], $errors[$filepath]);
        $this->assertSame(1, $linter->getNumberOfFilesChecked());
    }
}
