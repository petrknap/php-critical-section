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
            'single-lock' => 'string(18) "This was critical."' . PHP_EOL,
            'double-lock' => 'string(18) "This was critical."' . PHP_EOL,
            'array-lock' => 'string(18) "This was critical."' . PHP_EOL,
            'transactional' => null,
        ];
    }
}
