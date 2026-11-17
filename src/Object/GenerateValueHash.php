<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Object;

use Eldaqo\Collections\Traits\HashKeys;

/**
 * Provides value-based hashing for collections: equal contents produce equal hashes.
 * Classes using it implement getHash() by passing their elements to hashOf().
 * Note: a collection that is mutated while used as a key or element in another collection breaks that index.
 * @see Hashable
 */
trait GenerateValueHash
{
    use HashKeys;

    /**
     * @param list<mixed> $elements
     * @param bool $ordered Whether the order of the elements is part of the value.
     */
    private function hashOf(array $elements, bool $ordered): string
    {
        $parts = array_map($this->toTaggedKey(...), $elements);

        if (!$ordered) {
            sort($parts, SORT_STRING);
        }

        return hash('xxh3', static::class . implode('', $parts));
    }
}
