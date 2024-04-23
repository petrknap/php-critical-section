<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

final class CriticalSectionTest extends TestCase
{
    public function testCreatesCriticalSection(): void
    {
        self::assertInstanceOf(
            WrappingCriticalSection::class,
            CriticalSection::create()
        );
    }

    /** @dataProvider dataCreatesCriticalSectionWithLock */
    public function testCreatesCriticalSectionWithLock(bool $isBlocking): void
    {
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::once())
            ->method('acquire')
            ->with($isBlocking)
            ->willReturn(true);
        $lock->expects(self::once())
            ->method('release');

        $criticalSection = CriticalSection::withLock($lock, $isBlocking);
        $criticalSection(fn () => null);

        self::assertInstanceOf(
            SymfonyLockCriticalSection::class,
            $criticalSection
        );
    }

    public static function dataCreatesCriticalSectionWithLock(): array
    {
        return [
            'blocking' => [true],
            'non-blocking' => [false],
        ];
    }

    /** @dataProvider dataCreatesCriticalSectionWithLock */
    public function testCreatesCriticalSectionWithLocks(bool $isBlocking): void
    {
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::exactly(3))
            ->method('acquire')
            ->with($isBlocking)
            ->willReturn(true);
        $lock->expects(self::exactly(3))
            ->method('release');

        CriticalSection::withLocks([$lock, $lock, $lock], $isBlocking)(fn () => null);
    }
}
