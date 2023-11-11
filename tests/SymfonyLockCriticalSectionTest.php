<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Exception;
use PetrKnap\CriticalSection\Exception\CouldNotEnterCriticalSection;
use PetrKnap\CriticalSection\Exception\CouldNotLeaveCriticalSection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\NoLock;

class SymfonyLockCriticalSectionTest extends TestCase
{
    public function testAcquiresLockBeforeCriticalSectionIsExecuted(): void
    {
        $shared = new \stdClass();
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::once())
            ->method('acquire')
            ->willReturnCallback(function () use ($shared) {
                $shared->isLocked = true;
                return true;
            });

        CriticalSection::withLock($lock)(function () use ($shared) {
            self::assertTrue($shared->isLocked);
        });
    }

    public function testReturnsValueReturnedByExecutedCriticalSection(): void
    {
        $lock = new NoLock();
        $expected = new \stdClass();

        self::assertSame($expected, CriticalSection::withLock($lock)(function () use ($expected) {
            return $expected;
        }));
    }

    public function testReleasesLockAfterCriticalSectionWasExecuted(): void
    {
        $shared = new \stdClass();
        $shared->isLocked = true;
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->expects(self::once())
            ->method('release')
            ->willReturnCallback(function () use ($shared) {
                $shared->isLocked = false;
            });

        CriticalSection::withLock($lock)(function () use ($shared) {
            self::assertTrue($shared->isLocked);
        });

        self::assertFalse($shared->isLocked);
    }

    public function testReleasesLockAndThrowsWhenCriticalSectionThrows(): void
    {
        $shared = new \stdClass();
        $shared->isLocked = true;
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->expects(self::once())
            ->method('release')
            ->willReturnCallback(function () use ($shared) {
                $shared->isLocked = false;
            });
        $expectedException = new Exception();

        try {
            CriticalSection::withLock($lock)(function () use ($shared, $expectedException) {
                self::assertTrue($shared->isLocked);
                throw $expectedException;
            });
            self::fail();
        } catch (Exception $exception) {
            self::assertFalse($shared->isLocked);
            self::assertSame($expectedException, $exception);
        }
    }

    public function testSkipsCriticalSectionWhenItIsOccupiedInNonBlockingMode(): void
    {
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::once())
            ->method('acquire')
            ->willReturn(false);

        self::assertNull(CriticalSection::withLock($lock)(function (): bool {
            return true;
        }));
    }

    /** @dataProvider dataThrowsWhenLockThrowsOnAcquire */
    public function testThrowsWhenLockThrowsOnAcquire(Exception $lockException): void
    {
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::once())
            ->method('acquire')
            ->willThrowException($lockException);

        self::expectException(CouldNotEnterCriticalSection::class);
        CriticalSection::withLock($lock)(function () {
        });
    }

    public static function dataThrowsWhenLockThrowsOnAcquire(): iterable
    {
        $knownExceptions = [
            new LockConflictedException(),
            new LockAcquiringException(),
        ];
        foreach ($knownExceptions as $knownException) {
            yield get_class($knownException) => [$knownException];
        }
    }

    public function testThrowsWhenLockThrowsOnRelease(): void
    {
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->expects(self::once())
            ->method('release')
            ->willThrowException(new LockReleasingException());

        self::expectException(CouldNotLeaveCriticalSection::class);
        CriticalSection::withLock($lock)(function () {
        });
    }
}
