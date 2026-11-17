<?php

declare(strict_types=1);

use Tests\Doubles\Object\UsesGenerateSplObjectHash;

mutates(\Eldaqo\Collections\Object\GenerateSplObjectHash::class);

it('generates SPL object hash', function () {
    $object = new UsesGenerateSplObjectHash();
    expect($object->getHash())->toBe(spl_object_hash($object));
});
