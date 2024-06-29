# Critical section based on `symfony/lock`

The [CriticalSection](./src/CriticalSection.php) is a simple object that handles the critical section overhead for you
and lets you focus on the actual code.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lock = new NoLock();

$criticalOutput = CriticalSection::withLock($lock)(fn () => 'This was critical.');

var_dump($criticalOutput);
```

You can wrap critical sections one inside the other thanks to the [WrappingCriticalSection](./src/WrappingCriticalSection.php).
This makes it easy to combine multiple locks, for example.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lockA = new NoLock();
$lockB = new NoLock();

$criticalOutput = CriticalSection::withLock($lockA)->withLock($lockB)(fn () => 'This was critical.');

var_dump($criticalOutput);
```

You can also pass locks as array and leave the composition to the critical section.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lockA = new NoLock();
$lockB = new NoLock();

$criticalOutput = CriticalSection::withLocks([$lockA, $lockB])(fn () => 'This was critical.');

var_dump($criticalOutput);
```

## Do you need to accept only locked resources?

Use [`LockedResource`](./src/LockedResource.php) if you need to be sure that you are not processing resource outside it's critical section.

```php
namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\NoLock;

/** @param Locked<Some\Resource> $resource */
function f(Locked $resource) {
    echo $resource->value;
}

$lock = new NoLock();
$resource = LockableResource::of(new Some\Resource('data'), $lock);
CriticalSection::withLock($lock)(fn () => f($resource));
```

## Do you want to keep code clear?

To maintain clarity, I recommend creating a service with named critical sections.

```php
namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\NoLock;

class CriticalSectionService {
    /**
     * @param callable(Locked<Some\Resource> $from, Locked<Some\Resource> $to): void $section
     */
    public function moneyTransfer(callable $section, Some\Resource $from, Some\Resource $to): void {
        $fromLock = new NoLock();
        $lockedFrom = LockableResource::of($from, $fromLock);
        $toLock = new NoLock();
        $lockedTo = LockableResource::of($to, $toLock);
        $locks = [$from->value => $fromLock, $to->value => $toLock];
        ksort($locks); // force locking order
        CriticalSection::withLocks($locks)($section, $lockedFrom, $lockedTo);
    }
}

class Bank {
    public function __construct(
        private CriticalSectionService $criticalSection,
    ) {}

    public function transferMoney(Some\Resource $from, Some\Resource $to): void {
        $this->criticalSection->moneyTransfer([$this, 'doTransferMoney'], $from, $to);
    }

    /**
     * @param Locked<Some\Resource> $from
     * @param Locked<Some\Resource> $to
     */
    public function doTransferMoney(LockedResource $from, LockedResource $to): void {
        echo "Transferring money from {$from->value} to {$to->value}...";
    }
}

(new Bank(new CriticalSectionService()))->transferMoney(new Some\Resource('A'), new Some\Resource('B'));
```

## Does your critical section work with database?

Use [`doctrine/dbal`](https://packagist.org/packages/doctrine/dbal) and its `transactional` method.

```php
/** @var PetrKnap\CriticalSection\CriticalSectionInterface $criticalSection */
/** @var Doctrine\DBAL\Connection $connection */
$criticalSection(
    fn () => $connection->transactional(
        fn () => 'This was critical on DB server.'
    )
);
```

Always use `transactional` inside critical section to prevent starvation.

---

Run `composer require petrknap/critical-section` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
