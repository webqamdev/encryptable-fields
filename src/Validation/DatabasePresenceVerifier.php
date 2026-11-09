<?php

namespace Webqamdev\EncryptableFields\Validation;

use Illuminate\Database\Query\Expression;
use Illuminate\Validation\DatabasePresenceVerifier as BaseDatabasePresenceVerifier;

class DatabasePresenceVerifier extends BaseDatabasePresenceVerifier
{
    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param  string  $collection
     * @param  string  $column
     * @param  string  $value
     * @param  int|string|null  $excludeId
     * @param  string|null  $idColumn
     * @param  array<string, mixed>  $extra
     */
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = []): int
    {
        if (! empty($extra['is_encrypted'])) {
            $column = new Expression($column);
        } elseif (! empty($extra['is_hashed'])) {
            $value = dbHashValue((string) $value);
        }

        unset($extra['is_encrypted'], $extra['is_hashed']);

        return parent::getCount($collection, $column, $value, $excludeId, $idColumn, $extra);
    }

    /**
     * Count the number of objects in a collection with the given values.
     *
     * @param  string  $collection
     * @param  string  $column
     * @param  array<int, mixed>  $values
     * @param  array<string, mixed>  $extra
     */
    public function getMultiCount($collection, $column, array $values, array $extra = []): int
    {
        if (! empty($extra['is_hashed'])) {
            $values = array_map(fn (mixed $value): string => dbHashValue((string) $value), $values);
        }

        if (! empty($extra['is_encrypted'])) {
            $column = new Expression($column);
        }

        unset($extra['is_encrypted'], $extra['is_hashed']);

        return parent::getMultiCount($collection, $column, $values, $extra);
    }
}
