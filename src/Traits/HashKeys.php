<?php

declare(strict_types=1);

namespace Eldaqo\Collections\Traits;

use Eldaqo\Collections\Object\Hashable;

/**
 * @internal
 */
trait HashKeys
{
    /**
     * Type tag and length prefix keep e.g. 1 vs "1" and ["ab","c"] vs ["a","bc"] apart.
     */
    private function toInternalKey(mixed $publicKey): string
    {
        $part = gettype($publicKey) . ':' . match (gettype($publicKey)) {
            'string' => $publicKey,
            'integer', 'resource', 'resource (closed)' => $publicKey,
            // Exact bits: (string) depends on the precision ini and merges close floats. + 0.0 turns -0.0 into 0.0.
            'double' => bin2hex(pack('E', $publicKey + 0.0)),
            'object' => $publicKey instanceof Hashable ? $publicKey->getHash() : spl_object_hash($publicKey),
            'NULL' => '',
            'array' => implode('', array_map(
                fn (mixed $key, mixed $value) => $this->toInternalKey($key) . $this->toInternalKey($value),
                array_keys($publicKey),
                $publicKey,
            )),
            'boolean' => $publicKey ? 'true' : 'false',
            default => throw new \TypeError('Invalid key type'),
        };

        return strlen($part) . ':' . $part;
    }
}
