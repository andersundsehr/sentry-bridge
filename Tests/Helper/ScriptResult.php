<?php

declare(strict_types=1);

namespace AUS\SentryBridge\Tests\Helper;

use PHPUnit\Framework\Assert;

use function sprintf;

final readonly class ScriptResult
{
    public function __construct(
        public string $command,
        public string $output,
        public int $exitCode,
    ) {
    }

    public function emptyOutput(): bool
    {
        return trim($this->output) === '';
    }

    public function assertOk(): void
    {
        Assert::assertTrue($this->exitCode === 0, sprintf(
            'Script "%s" failed with exit code %d. Output: %s',
            $this->command,
            $this->exitCode,
            $this->output
        ));
    }

    public function assertError(): void
    {
        Assert::assertTrue($this->exitCode !== 0, sprintf(
            'Script "%s" succeeded with exit code %d. Output: %s',
            $this->command,
            $this->exitCode,
            $this->output
        ));
    }

    public function assertOutput(string $containsThisText): void
    {
        Assert::assertStringContainsString($containsThisText, $this->output, sprintf(
            'Script "%s" output does not contain expected text "%s". Output: %s',
            $this->command,
            $containsThisText,
            $this->output
        ));
    }
}
