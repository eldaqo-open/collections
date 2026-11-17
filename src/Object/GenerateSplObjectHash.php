<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Object;

/**
 * Provides a simple Hashable implementation for objects using the SPL extension.
 *
 * The hash is based on object identity, not value: two distinct instances with identical property values get
 * different hashes. If value-based hashes are required, implement Hashable manually in the class.
 * @see Hashable
 */
trait GenerateSplObjectHash
{
    public function getHash(): string
    {
        return spl_object_hash($this);
    }
}
