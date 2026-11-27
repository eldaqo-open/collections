<?php

declare(strict_types=1);

namespace Eldaqo\Collections;

use Eldaqo\Collections\Contracts\ISet;
use Eldaqo\Collections\Object\GenerateValueHash;
use Eldaqo\Collections\Object\Hashable;
use Eldaqo\Collections\Traits\CollectionShared;
use Eldaqo\Collections\Traits\CountsElements;
use Eldaqo\Collections\Traits\HashKeys;
use Eldaqo\Collections\Traits\ListShared;
use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;

/**
 * Represents a list of unique items.
 *
 * @template T
 *
 * @implements ISet<T>
 */
final class Set implements ISet, Hashable
{
    use GenerateValueHash;
    use CollectionShared;
    use HashKeys;
    /** @use ListShared<T> */
    use ListShared;
    use CountsElements;

    /**
     * @var array<string, int>
     */
    private array $keys = [];

    /**
     * @var list<T>
     */
    private array $values = [];

    /**
     * @param T[] $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function getHash(): string
    {
        return $this->hashOf($this->values, false);
    }

    public function apply(callable $selector): void
    {
        $values = array_map($selector, $this->values, array_keys($this->values));
        $this->assertIsNotSelf(...$values);

        $result = new self($values);

        // Checked before assigning, so a failing selector leaves the set unchanged.
        if ($result->count() !== $this->count()) {
            throw new InvalidArgumentException('The selector produced duplicate values.');
        }

        $this->values = $result->values;
        $this->keys = $result->keys;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException('The specified key does not exist.');
        }

        return $this->values[$offset];
    }

    /**
     * @inheritdoc
     * @param int|null $offset
     * @param T $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);
            return;
        }

        // The offset is stored in $keys, which must hold real integers.
        if (!is_int($offset)) {
            throw new \TypeError('The offset must be an integer.');
        }

        if ($offset < 0 || $offset >= $this->count()) {
            throw new OutOfRangeException("The offset ($offset) must be between 0 and the length ({$this->count()}) of the list");
        }

        $this->assertIsNotSelf($value);

        $internalKey = $this->toInternalKey($value);
        $oldInternalKey = $this->toInternalKey($this->values[$offset]);

        if ($internalKey !== $oldInternalKey && array_key_exists($internalKey, $this->keys)) {
            throw new InvalidArgumentException('The specified value is already present in the set.');
        }

        unset($this->keys[$oldInternalKey]);
        $this->values[$offset] = $value;
        $this->keys[$internalKey] = $offset;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException('The specified key does not exist.');
        }

        $this->remove($this->values[$offset]);
    }

    public function contains(mixed $value): bool
    {
        return array_key_exists($this->toInternalKey($value), $this->keys);
    }

    public function search(mixed $value): int|null
    {
        return $this->keys[$this->toInternalKey($value)] ?? null;
    }

    public function add(mixed $value): void
    {
        $this->assertIsNotSelf($value);

        $internalKey = $this->toInternalKey($value);

        if (array_key_exists($internalKey, $this->keys)) {
            return;
        }

        $this->keys[$internalKey] = count($this->values);
        $this->values[$this->keys[$internalKey]] = $value;
    }

    public function remove(mixed $value): void
    {
        $internalKey = $this->toInternalKey($value);

        if (!array_key_exists($internalKey, $this->keys)) {
            throw new OutOfBoundsException('The specified value is not present in the set.');
        }

        $index = $this->keys[$internalKey];

        unset($this->keys[$internalKey]);
        array_splice($this->values, $index, 1);

        // Shift the later positions down instead of hashing every value again.
        foreach ($this->keys as $key => $offset) {
            if ($offset > $index) { // @pest-mutate-ignore GreaterToGreaterOrEqual: only the removed key equals $index, and it is already unset
                $this->keys[$key] = $offset - 1;
            }
        }
    }

    public function pop(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $item */
        $item = array_pop($this->values);

        unset($this->keys[$this->toInternalKey($item)]);

        return $item;
    }

    public function push(mixed ...$values): void
    {
        // Checked up front, so a rejected push leaves the set unchanged.
        $this->assertIsNotSelf(...$values);

        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function shift(): mixed
    {
        $this->assertIsNotEmpty();

        $item = $this->values[0];
        $this->remove($item);

        return $item;
    }

    public function sortBy(callable $selector): Set
    {
        usort($this->values, $selector);

        $this->keys = [];
        foreach ($this->values as $index => $value) {
            $this->keys[$this->toInternalKey($value)] = $index;
        }

        return $this;
    }

    /**
     * @return \Generator<int, T>
     */
    public function getIterator(): \Generator
    {
        yield from $this->values;
    }
}
