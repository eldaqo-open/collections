<?php

declare(strict_types=1);

namespace Eldaqo\Collections;

use Eldaqo\Collections\Contracts\IDictionary;
use Eldaqo\Collections\Contracts\IList;
use Eldaqo\Collections\Object\GenerateValueHash;
use Eldaqo\Collections\Object\Hashable;
use Eldaqo\Collections\Traits\CollectionShared;
use Eldaqo\Collections\Traits\CountsElements;
use Eldaqo\Collections\Traits\ListShared;
use OutOfBoundsException;
use OutOfRangeException;

/**
 * Represents a list of items.
 *
 * @template T
 *
 * @implements IList<T>
 */
final class ArrayList implements IList, Hashable
{
    use GenerateValueHash;
    use CollectionShared;
    /** @use ListShared<T> */
    use ListShared;
    use CountsElements;

    /**
     * @var list<T>
     */
    private array $values;

    /**
     * @param T[] $items
     */
    public function __construct(array $items = [])
    {
        $this->values = array_values($items);
    }

    public function getHash(): string
    {
        return $this->hashOf($this->values, true);
    }

    public function apply(callable $selector): void
    {
        $values = array_map($selector, $this->values, array_keys($this->values));
        $this->assertIsNotSelf(...$values);

        $this->values = $values;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function &offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("The specified key ($offset) does not exist.");
        }

        return $this->values[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offset ??= $this->count();

        // PHP keeps non-canonical numeric strings like "01" as string keys, which would break the list.
        if (!is_int($offset)) {
            throw new \TypeError('The offset must be an integer.');
        }

        if ($offset < 0 || $offset > $this->count()) {
            throw new OutOfRangeException("The offset ($offset) must be between 0 and the length ({$this->count()}) of the list");
        }

        $this->assertIsNotSelf($value);
        $this->values[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("The specified key ($offset) does not exist.");
        }

        unset($this->values[$offset]);
        $this->values = array_values($this->values);
    }

    public function contains(mixed $value): bool
    {
        return $this->search($value) !== null;
    }

    public function countValues(): IDictionary
    {
        return $this->toDictionary()->countValues();
    }

    public function padLeft(int $length, mixed $value): self
    {
        if ($length < 1) {
            throw new OutOfRangeException('The length must be greater than 0.');
        }

        $this->pad(-$length, $value);

        return $this;
    }

    public function padRight(int $length, mixed $value): self
    {
        if ($length < 1) {
            throw new OutOfRangeException('The length must be greater than 0.');
        }

        $this->pad($length, $value);

        return $this;
    }

    private function pad(int $length, mixed $value): void
    {
        $this->assertIsNotSelf($value);
        $this->values = array_pad($this->values, $length, $value);
    }

    public function pop(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $value */
        $value = array_pop($this->values);

        return $value;
    }

    public function push(mixed ...$values): void
    {
        $this->assertIsNotSelf(...$values);
        array_push($this->values, ...$values);
    }

    /**
     * @inheritdoc
     * @return ArrayList<int>
     */
    public function searchAll(callable $selector): ArrayList
    {
        $keys = [];

        foreach ($this->values as $key => $value) {
            if ($selector($value)) {
                $keys[] = $key;
            }
        }

        return new ArrayList($keys);
    }

    public function shift(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $value */
        $value = array_shift($this->values);

        return $value;
    }

    public function sortBy(callable $selector): ArrayList
    {
        usort($this->values, $selector);

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
