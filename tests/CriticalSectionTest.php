<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockInterface;

final class CriticalSectionTest extends CriticalSectionTestCase
{
    use CriticalSectionStaticFactory;

    /**
     * @dataProvider dataCreatesCriticalSectionWithLock
     */
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
        self::assertInstanceOf(
            Symfony\Lock\CriticalSection::class,
            $criticalSection
        );
        $criticalSection(static fn () => null);
    }

    public static function dataCreatesCriticalSectionWithLock(): array
    {
        return [
            'blocking' => [true],
            'non-blocking' => [false],
        ];
    }

    /**
     * @dataProvider dataCreatesCriticalSectionWithLock
     */
    public function testCreatesCriticalSectionWithLocks(bool $isBlocking): void
    {
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::exactly(3))
            ->method('acquire')
            ->with($isBlocking)
            ->willReturn(true);
        $lock->expects(self::exactly(3))
            ->method('release');

        $criticalSection = CriticalSection::withLocks([$lock, $lock, $lock], $isBlocking);
        self::assertInstanceOf(
            Symfony\Lock\CriticalSection::class,
            $criticalSection
        );
        $criticalSection(static fn () => null);
    }

    protected function createCriticalSection(bool $isBlocking): CriticalSection|null
    {
        return $isBlocking ? null : self::nonCritical(canEnter: true);
    }

    protected function createUnenterableCriticalSection(bool $isBlocking): CriticalSection|null
    {
        return $isBlocking ? null : self::nonCritical(canEnter: false);
    }

    protected function createUnleavableCriticalSection(): CriticalSection|null
    {
        return null;
    }
}
