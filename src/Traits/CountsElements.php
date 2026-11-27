<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Traits;

/**
 * @internal
 */
trait CountsElements
{
    public function count(): int
    {
        return count($this->values);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    private function assertIsNotEmpty(): void
    {
        if ($this->isEmpty()) {
            throw new \UnderflowException('The collection is empty.');
        }
    }

    private function assertCanPick(int $count): void
    {
        $this->assertIsNotEmpty();

        if ($count < 1 || $count > $this->count()) {
            throw new \OutOfRangeException('The count must be between 1 and the number of elements in the collection.');
        }
    }
}
