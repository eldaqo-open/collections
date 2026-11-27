<?php

declare(strict_types=1);

namespace Eldaqo\Collections;

if (!function_exists(__NAMESPACE__ . '\\listOf')) {
    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return ArrayList<T>
     */
    function listOf(array $items): ArrayList
    {
        return new ArrayList($items);
    }
}

if (!function_exists(__NAMESPACE__ . '\\setOf')) {
    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return Set<T>
     */
    function setOf(array $items): Set
    {
        return new Set($items);
    }
}

if (!function_exists(__NAMESPACE__ . '\\dictionaryOf')) {
    /**
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $items
     *
     * @return Dictionary<TKey, TValue>
     */
    function dictionaryOf(array $items): Dictionary
    {
        $pairs = [];
        foreach ($items as $key => $value) {
            $pairs[] = new Pair($key, $value);
        }

        return new Dictionary(...$pairs);
    }
}
