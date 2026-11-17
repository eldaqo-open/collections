<?php

declare(strict_types=1);

use Eldaqo\Collections\Pair;

mutates(\Eldaqo\Collections\Pair::class);

it('returns the key and value of the pair correctly', function () {
    $pair = new Pair(1, 'a');
    expect($pair->key())->toBe(1)
        ->and($pair->value())->toBe('a');
});

it('returns a new pair with the specified key', function () {
    $pair = new Pair(1, 'a');
    $newPair = $pair->withKey(2);
    expect($newPair->key())->toBe(2)
        ->and($newPair->value())->toBe('a');
});

it('returns a new pair with the specified value', function () {
    $pair = new Pair(1, 'a');
    $newPair = $pair->withValue('b');
    expect($newPair->key())->toBe(1)
        ->and($newPair->value())->toBe('b');
});

it('generates equal hashes for equal pairs and different hashes otherwise', function () {
    expect((new Pair(1, 'a'))->getHash())->toBe((new Pair(1, 'a'))->getHash())
        ->and((new Pair(1, 'a'))->getHash())->not->toBe((new Pair('1', 'a'))->getHash())
        ->and((new Pair(1, 'a'))->getHash())->not->toBe((new Pair('a', 1))->getHash());
});

it('generates different hashes for pairs with different values', function () {
    expect((new Pair(1, 'a'))->getHash())->not->toBe((new Pair(1, 'b'))->getHash());
});
