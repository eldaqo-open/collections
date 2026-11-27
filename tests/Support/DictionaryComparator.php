<?php

declare(strict_types=1);

namespace Tests\Support;

use Eldaqo\Collections\Dictionary;
use SebastianBergmann\Comparator\Comparator;

/**
 * PHPUnit compares string-keyed arrays without looking at order, but dictionaries are ordered,
 * so dictionaries are compared as ordered lists of [key, value] pairs.
 */
final class DictionaryComparator extends Comparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected instanceof Dictionary && $actual instanceof Dictionary;
    }

    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        $expectedPairs = self::pairs($expected);
        $actualPairs = self::pairs($actual);

        $this->factory()
            ->getComparatorFor($expectedPairs, $actualPairs)
            ->assertEquals($expectedPairs, $actualPairs, $delta, $canonicalize, $ignoreCase);
    }

    /**
     * @param Dictionary<mixed, mixed> $dictionary
     *
     * @return list<array{mixed, mixed}>
     */
    private static function pairs(Dictionary $dictionary): array
    {
        $pairs = [];

        foreach ($dictionary as $key => $value) {
            $pairs[] = [$key, $value];
        }

        return $pairs;
    }
}
