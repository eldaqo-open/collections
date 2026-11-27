<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Traits;

use Eldaqo\Collections\ArrayList;
use Eldaqo\Collections\Contracts\IDictionary;
use Eldaqo\Collections\Contracts\IList;
use Eldaqo\Collections\Contracts\ISet;
use Eldaqo\Collections\Dictionary;
use Eldaqo\Collections\Pair;
use Eldaqo\Collections\Set;
use Eldaqo\Collections\SortDirection;
use Random\Randomizer;

/**
 * @internal
 *
 * @template T
 */
trait ListShared
{
    public function keys(): ISet
    {
        return new Set(array_keys($this->values));
    }

    public function values(): IList
    {
        return new ArrayList($this->values);
    }

    public function toSet(): ISet
    {
        return new Set($this->values);
    }

    public function toDictionary(): IDictionary
    {
        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($key, $value),
            array_keys($this->values),
            $this->values
        ));
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function chunk(int $length): IList
    {
        return new ArrayList(array_map(fn (array $chunk) => new self($chunk), array_chunk($this->values, $length)));
    }

    /**
     * @inheritdoc
     * @return self<T>
     */
    public function filter(callable $selector): self
    {
        return new self(array_filter($this->values, $selector, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @inheritdoc
     *
     * @template TNewValue
     *
     * @param callable(T, int=): TNewValue $selector
     *
     * @return self<TNewValue>
     */
    public function map(callable $selector): self
    {
        return new self(array_map($selector, $this->values, array_keys($this->values)));
    }

    public function flip(): IDictionary
    {
        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($value, $key),
            array_keys($this->values),
            $this->values
        ));
    }

    /**
     * @return IDictionary<int, T>
     */
    private function pick(int $count, Randomizer $randomizer): IDictionary
    {
        $this->assertCanPick($count);

        return new Dictionary(...array_map(
            fn ($key) => new Pair($key, $this->values[$key]),
            $randomizer->pickArrayKeys($this->values, $count)
        ));
    }

    public function reduce(callable $selector, mixed $initial = null): mixed
    {
        return array_reduce($this->values, $selector, $initial);
    }

    public function search(mixed $value): int|null
    {
        // For these types hash equality is exactly ===, so the native search gives the same result.
        // Speed only: skipping this branch gives the same result, so its mutants cannot be killed.
        if ($value === null || is_int($value) || is_string($value) || is_bool($value)) { // @pest-mutate-ignore BooleanOrToBooleanAnd
            $offset = array_search($value, $this->values, true);
            return $offset === false ? null : $offset; // @pest-mutate-ignore RemoveEarlyReturn
        }

        $type = gettype($value);
        $needle = $this->toInternalKey($value);

        foreach ($this->values as $offset => $item) {
            // The type is part of the hash, so items of another type are skipped without hashing them.
            if ($item === $value || (gettype($item) === $type && $this->toInternalKey($item) === $needle)) {
                return $offset;
            }
        }

        return null;
    }

    public function sort(SortDirection $direction = SortDirection::ASCENDING): self
    {
        /** @var callable(T, T): int<-1, 1> $selector */
        $selector = static fn ($a, $b) => $direction->value * ($a <=> $b);
        return $this->sortBy($selector);
    }
}
