<?php

declare(strict_types=1);

namespace Eldaqo\Collections;

use Eldaqo\Collections\Contracts\IDictionary;
use Eldaqo\Collections\Contracts\IList;
use Eldaqo\Collections\Contracts\IPair;
use Eldaqo\Collections\Contracts\ISet;
use Eldaqo\Collections\Object\GenerateValueHash;
use Eldaqo\Collections\Object\Hashable;
use Eldaqo\Collections\Traits\CollectionShared;
use Eldaqo\Collections\Traits\CountsElements;
use Eldaqo\Collections\Traits\HashKeys;
use OutOfBoundsException;
use Random\Randomizer;
use ValueError;

/**
 * Represents a collection of key-value pairs.
 *
 * @template TKey
 * @template TValue
 *
 * @implements IDictionary<TKey, TValue>
 */
final class Dictionary implements IDictionary, Hashable
{
    use GenerateValueHash;
    use CollectionShared;
    use HashKeys;
    use CountsElements;

    /**
     * Keyed by internal key; PHP arrays keep insertion order, so no separate index is needed.
     *
     * @var array<string, IPair<TKey, TValue>>
     */
    private array $values = [];

    /**
     * @param IPair<TKey, TValue> ...$pairs
     */
    public function __construct(IPair ...$pairs)
    {
        foreach ($pairs as $pair) {
            $this->offsetSet($pair->key(), $pair->value());
        }
    }

    public static function combine(ISet $keys, IList $values): IDictionary
    {
        if ($keys->count() !== $values->count()) {
            throw new ValueError('The number of keys and values do not match.');
        }

        return new self(
            ...array_map(
                static fn (mixed $key, mixed $value) => new Pair($key, $value),
                $keys->toArray(),
                $values->toArray()
            )
        );
    }

    public function getHash(): string
    {
        $pairHashes = array_map(fn (IPair $pair) => $this->hashOf([$pair->key(), $pair->value()], true), $this->values);

        return $this->hashOf($pairHashes, false);
    }

    public function keys(): ISet
    {
        return new Set(array_map(fn (IPair $pair) => $pair->key(), $this->values));
    }

    public function values(): IList
    {
        return new ArrayList(array_map(fn (IPair $pair) => $pair->value(), $this->values));
    }

    public function toSet(): ISet
    {
        return $this->toList()->toSet();
    }

    public function toDictionary(): IDictionary
    {
        return clone $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($this->toInternalKey($offset), $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $internalKey = $this->toInternalKey($offset);

        if (!array_key_exists($internalKey, $this->values)) {
            throw new OutOfBoundsException('The specified key does not exist.');
        }

        return $this->values[$internalKey]->value();
    }

    /**
     * @inheritdoc
     * @param TKey $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertIsNotSelf($offset, $value);

        /** @var Pair<TKey, TValue> $pair */
        $pair = new Pair($offset, $value);

        $this->values[$this->toInternalKey($offset)] = $pair;
    }

    public function offsetUnset(mixed $offset): void
    {
        $internalKey = $this->toInternalKey($offset);

        if (!array_key_exists($internalKey, $this->values)) {
            throw new OutOfBoundsException('The specified key does not exist.');
        }

        unset($this->values[$internalKey]);
    }

    public function apply(callable $selector): void
    {
        $values = array_map(
            static fn (IPair $pair) => $pair->withValue($selector($pair->value(), $pair->key())),
            $this->values
        );
        $this->assertIsNotSelf(...array_map(static fn (IPair $pair) => $pair->value(), $values));

        $this->values = $values;
    }

    public function chunk(int $length): IList
    {
        return new ArrayList(array_map(fn (mixed $valueChunk) => new Dictionary(...$valueChunk), array_chunk($this->values, $length)));
    }

    public function contains(mixed $value): bool
    {
        return $this->search($value) !== null;
    }

    public function countValues(): IDictionary
    {
        $result = new self();

        // Writes the internal storage directly, so each value is hashed once instead of three times.
        foreach ($this->values as $pair) {
            $internalKey = $this->toInternalKey($pair->value());
            $count = isset($result->values[$internalKey]) ? $result->values[$internalKey]->value() + 1 : 1;
            $result->values[$internalKey] = new Pair($pair->value(), $count);
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @return self<TKey, TValue>
     */
    public function filter(callable $selector): self
    {
        $result = new self();
        $result->values = array_filter(
            $this->values,
            static fn (IPair $pair) => $selector($pair->value(), $pair->key())
        );

        return $result;
    }

    public function flip(): IDictionary
    {
        return new self(
            ...array_map(
                static fn (IPair $pair) => new Pair($pair->value(), $pair->key()),
                $this->values
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @template TNewValue
     *
     * @param callable(TValue, TKey=): TNewValue $selector
     *
     * @return self<TKey, TNewValue>
     */
    public function map(callable $selector): self
    {
        // Keys are unchanged, so the internal keys are reused instead of hashing every key again.
        $result = new self();
        $result->values = array_map(
            static fn (IPair $pair) => $pair->withValue($selector($pair->value(), $pair->key())),
            $this->values
        );

        return $result;
    }

    public function pop(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var Pair<TKey, TValue> $item */
        $item = array_pop($this->values);

        return $item->value();
    }

    /**
     * @return IDictionary<TKey, TValue>
     */
    private function pick(int $count, Randomizer $randomizer): IDictionary
    {
        $this->assertCanPick($count);

        return new self(...array_map(
            fn (string $key) => $this->values[$key],
            $randomizer->pickArrayKeys($this->values, $count)
        ));
    }

    public function reduce(callable $selector, mixed $initial = null): mixed
    {
        return $this->values()->reduce($selector, $initial);
    }

    public function search(mixed $value): mixed
    {
        $offset = $this->values()->search($value);

        return $offset === null ? null : array_values($this->values)[$offset]->key();
    }

    /**
     * @inheritdoc
     * @return ArrayList<TKey>
     */
    public function searchAll(callable $selector): ArrayList
    {
        $keys = [];

        foreach ($this->values as $pair) {
            if ($selector($pair->value())) {
                $keys[] = $pair->key();
            }
        }

        return new ArrayList($keys);
    }

    public function shift(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var Pair<TKey, TValue> $item */
        $item = array_shift($this->values);

        return $item->value();
    }

    public function sort(SortDirection $direction = SortDirection::ASCENDING): Dictionary
    {
        /** @var callable(IPair<TKey, TValue>, IPair<TKey, TValue>): int<-1, 1> $selector */
        $selector = static fn ($a, $b) => $direction->value * ($a->value() <=> $b->value());
        return $this->sortBy($selector);
    }

    public function sortBy(callable $selector): Dictionary
    {
        uasort($this->values, $selector);

        return $this;
    }

    /**
     * @return \Generator<TKey, TValue>
     */
    public function getIterator(): \Generator
    {
        foreach ($this->values as $pair) {
            yield $pair->key() => $pair->value();
        }
    }
}
