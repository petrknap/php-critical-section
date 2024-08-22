# Critical section based on `symfony/lock`

[The `CriticalSection`](./src/CriticalSection.php) is a simple object that handles the critical section overhead for you
and lets you focus on the actual code.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lock = new NoLock();

$criticalOutput = CriticalSection::withLock($lock)(fn () => 'This was critical.');

var_dump($criticalOutput);
```

You can wrap critical sections one inside the other thanks to [the `WrappingCriticalSection`](./src/WrappingCriticalSection.php).
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

Use [the `LockedResource`](./src/LockedResource.php) if you need to be sure that you are not processing resource outside it's critical section.

```php
namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\NoLock;

/**
 * @param Locked<Some\Resource> $from
 * @param Locked<Some\Resource> $to
 */
function transferValue(LockedResource $from, LockedResource $to, int $volume): void {
    if ($from->value < $volume) {
        throw new \RuntimeException();
    }
    $from->value -= $volume;
    $to->value += $volume;
    echo "Moved {$volume} from #{$from->id} (current value {$from->value}) to #{$to->id} (current value {$to->value}).";
}

$fromLock = new NoLock();
$lockedFrom = LockableResource::of(new Some\Resource(1, value: 15), $fromLock);
$toLock = new NoLock();
$lockedTo = LockableResource::of(new Some\Resource(2, value: 5), $toLock);
CriticalSection::withLocks([$fromLock, $toLock])(fn () => transferValue($lockedFrom, $lockedTo, 10));
```

## Do you want to keep code clear?

To maintain clarity, I recommend using your own named critical sections (as service),
like [`Some\NamedCriticalSectionService`](./tests/Some/NamedCriticalSectionService.php).

```php
namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;

$criticalSection = new Some\NamedCriticalSectionService(new LockFactory(new InMemoryStore()));

$criticalSection->updateSomeResources(
     fn (LockedResource $from, LockedResource $to) => transferValue($from, $to, 10),
     new Some\Resource(1, value: 15),
     new Some\Resource(2, value: 5),
);
```

## Does your critical section work with database?

Use [the `doctrine/dbal`](https://packagist.org/packages/doctrine/dbal) and its `transactional` method.

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
