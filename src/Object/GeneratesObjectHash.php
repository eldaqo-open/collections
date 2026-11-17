<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Object;

/**
 * Provides a simple Hashable implementation for objects using the SPL extension.
 * @see Hashable
 */
trait GeneratesObjectHash
{
    public function getHash(): string
    {
        return spl_object_hash($this);
    }
}
