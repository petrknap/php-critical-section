<?php declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\Shorts\PhpUnit\MarkdownFileTestInterface;
use PetrKnap\Shorts\PhpUnit\MarkdownFileTestTrait;
use PHPUnit\Framework\TestCase;

class ReadmeTest extends TestCase implements MarkdownFileTestInterface
{
    use MarkdownFileTestTrait;

    public static function getPathToMarkdownFile(): string
    {
        return __DIR__ . '/../README.md';
    }

    public static function getExpectedOutputsOfPhpExamples(): iterable
    {
        return [
            'single_lock' => 'string(18) "This was critical!"' . PHP_EOL,
            'double_lock' => 'string(28) "This was even more critical!"' . PHP_EOL,
        ];
    }
}
