<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Contracts;

use Eldaqo\Collections\SortDirection;

/**
 * Represents a collection of items, where each item is associated with a unique key. The keys may be any of the
 * following types: string, integer, float, resource, object, boolean, array or null.
 *
 * @template TKey
 * @template TValue
 *
 * @extends ICollection<TKey, TValue>
 */
interface IDictionary extends ICollection
{
    /**
     * Creates a dictionary by combining the given set of keys with the list of values.
     *
     * @template TNewKey
     * @template TNewValue
     *
     * @param ISet<TNewKey> $keys
     * @param IList<TNewValue> $values
     *
     * @return IDictionary<TNewKey, TNewValue>
     *
     * @throws \ValueError if the number of keys and values do not match.
     */
    public static function combine(ISet $keys, IList $values): IDictionary;

    /**
     * Returns a dictionary, where the keys are the unique values in the collection, and the values are the number of occurrences.
     *
     * @return IDictionary<TValue, int>
     */
    public function countValues(): IDictionary;

    /**
     * Filtering the dictionary by passing each element in it to the given callback. If the callback returns true, the element will be included in the resulting dictionary.
     *
     * @param callable(TValue, TKey=): bool $selector
     *
     * @return IDictionary<TKey, TValue>
     */
    public function filter(callable $selector): IDictionary;

    /**
     * Search for all items in the dictionary that match the given selector, and return a list of their corresponding keys.
     *
     * @param callable(TValue): bool $selector
     *
     * @return IList<TKey>
     */
    public function searchAll(callable $selector): IList;

    /**
     * Sorts the items of the dictionary in the given order.
     *
     * @param SortDirection $direction
     *
     * @return $this
     */
    public function sort(SortDirection $direction = SortDirection::ASCENDING): self;

    /**
     * Sorts the items of the dictionary by the given selector.
     *
     * @param callable(IPair<TKey, TValue>, IPair<TKey, TValue>): int<-1, 1> $selector
     *
     * @return $this
     */
    public function sortBy(callable $selector): self;
}
