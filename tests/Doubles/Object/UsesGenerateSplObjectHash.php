<?php

declare(strict_types=1);

namespace Tests\Doubles\Object;

use Eldaqo\Collections\Object\GenerateSplObjectHash;
use Eldaqo\Collections\Object\Hashable;

final readonly class UsesGenerateSplObjectHash implements Hashable
{
    use GenerateSplObjectHash;
    
    public function __construct(
        public int $field = 123
    ) {}
}