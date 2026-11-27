<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a collection would contain itself, since hashing it would then recurse forever.
 */
final class SelfReferenceException extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('A collection cannot contain itself.');
    }
}
