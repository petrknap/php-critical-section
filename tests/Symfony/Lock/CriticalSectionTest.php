<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Symfony\Lock;

use Exception;
use PetrKnap\CriticalSection\CriticalSection;
use PetrKnap\CriticalSection\CriticalSectionTestCase;
use stdClass;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockInterface;

final class CriticalSectionTest extends CriticalSectionTestCase
{
    public function testAcquiresLockBeforeCriticalSectionIsExecuted(): void
    {
        $shared = new stdClass();
        $shared->isLocked = false;
        $lock = self::createMock(LockInterface::class);
        $lock->expects(self::once())
            ->method('acquire')
            ->willReturnCallback(static function () use ($shared) {
                $shared->isLocked = true;
                return true;
            });

        CriticalSection::withLock($lock)(static function () use ($shared) {
            self::assertTrue($shared->isLocked);
        });
    }

    public function testReleasesLockAfterCriticalSectionWasExecuted(): void
    {
        $shared = new stdClass();
        $shared->isLocked = true;
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->expects(self::once())
            ->method('release')
            ->willReturnCallback(static function () use ($shared) {
                $shared->isLocked = false;
            });

        CriticalSection::withLock($lock)(static function () use ($shared) {
            self::assertTrue($shared->isLocked);
        });

        self::assertFalse($shared->isLocked);
    }

    public function testReleasesLockAndThrowsWhenCriticalSectionThrows(): void
    {
        $shared = new stdClass();
        $shared->isLocked = true;
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->expects(self::once())
            ->method('release')
            ->willReturnCallback(static function () use ($shared) {
                $shared->isLocked = false;
            });
        $expectedException = new Exception();

        try {
            CriticalSection::withLock($lock)(static function () use ($shared, $expectedException) {
                self::assertTrue($shared->isLocked);
                throw $expectedException;
            });
            self::fail();
        } catch (Exception $exception) {
            self::assertFalse($shared->isLocked);
            self::assertSame($expectedException, $exception);
        }
    }

    protected function createCriticalSection(bool $isBlocking): CriticalSection|null
    {
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);

        return CriticalSection::withLock($lock);
    }

    protected function createUnenterableCriticalSection(bool $isBlocking): CriticalSection|null
    {
        $lock = self::createMock(LockInterface::class);
        if ($isBlocking) {
            $lock->method('acquire')->willThrowException(self::createStub(LockConflictedException::class));
        } else {
            $lock->method('acquire')->willReturn(false);
        }

        return CriticalSection::withLock($lock);
    }

    protected function createUnleavableCriticalSection(): CriticalSection|null
    {
        $lock = self::createMock(LockInterface::class);
        $lock->method('acquire')->willReturn(true);
        $lock->method('release')->willThrowException(self::createStub(LockReleasingException::class));

        return CriticalSection::withLock($lock);
    }
}
