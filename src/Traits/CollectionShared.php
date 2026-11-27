<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Traits;

use Eldaqo\Collections\Contracts\ICollection;
use Eldaqo\Collections\Contracts\IDictionary;
use Eldaqo\Collections\Contracts\IList;
use Eldaqo\Collections\Exceptions\SelfReferenceException;
use Eldaqo\Collections\Set;
use Random\Engine\Mt19937;
use Random\Engine\Secure;
use Random\Randomizer;

/**
 * @internal
 */
trait CollectionShared
{
    public function toList(): IList
    {
        return $this->values();
    }

    public function merge(ICollection ...$collections): IDictionary
    {
        $merged = $this->toDictionary();

        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    // Set lookups compare like the rest of the library (1 !== "1", objects by hash), unlike array_diff's string casts.
    public function diff(ICollection ...$collections): IDictionary
    {
        $excluded = new Set();

        foreach ($collections as $collection) {
            $excluded->push(...$collection->values()->toArray());
        }

        return $this->toDictionary()->filter(static fn (mixed $value) => !$excluded->contains($value));
    }

    public function intersect(ICollection ...$collections): IDictionary
    {
        // toSet() is for O(1) lookups only; contains() gives the same answer on any collection.
        $sets = array_map(static fn (ICollection $collection) => $collection->toSet(), $collections); // @pest-mutate-ignore UnwrapArrayMap

        return $this->toDictionary()->filter(static function (mixed $value) use ($sets) {
            foreach ($sets as $set) {
                if (!$set->contains($value)) {
                    return false;
                }
            }

            return true;
        });
    }

    public function random(int $count = 1): IDictionary
    {
        return $this->pick($count, new Randomizer(new Mt19937()));
    }

    public function secureRandom(int $count = 1): IDictionary
    {
        return $this->pick($count, new Randomizer(new Secure()));
    }

    // Only direct self-containment is caught; an indirect cycle (a contains b contains a) still recurses when hashed.
    private function assertIsNotSelf(mixed ...$values): void
    {
        foreach ($values as $value) {
            if ($value === $this) {
                throw new SelfReferenceException();
            }
        }
    }

    public function any(callable $selector): bool
    {
        foreach ($this as $value) {
            if ($selector($value)) {
                return true;
            }
        }

        return false;
    }

    public function every(callable $selector): bool
    {
        foreach ($this as $value) {
            if (!$selector($value)) {
                return false;
            }
        }

        return true;
    }
}
