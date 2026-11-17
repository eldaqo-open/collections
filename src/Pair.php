<?php

declare(strict_types=1);

namespace Eldaqo\Collections;

use Eldaqo\Collections\Contracts\IPair;
use Eldaqo\Collections\Object\GenerateValueHash;

/**
 * Represents an immutable key-value pair.
 *
 * @template TKey
 * @template TValue
 *
 * @implements IPair<TKey, TValue>
 */
final readonly class Pair implements IPair
{
    use GenerateValueHash;

    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function __construct(
        private mixed $key,
        private mixed $value
    ) {
    }

    public function getHash(): string
    {
        return $this->hashOf([$this->key, $this->value], true);
    }

    public function key(): mixed
    {
        return $this->key;
    }

    public function withKey(mixed $key): IPair
    {
        return new self($key, $this->value);
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function withValue(mixed $value): IPair
    {
        return new self($this->key, $value);
    }
}
