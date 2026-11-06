<?php

namespace Webqamdev\EncryptableFields\Validation;

use Illuminate\Support\Facades\DB;
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
        $query = $this->table($collection);

        if (empty($extra['is_encrypted'])) {
            if (! empty($extra['is_hashed'])) {
                $value = dbHashValue($value);
                unset($extra['is_hashed']);
            }

            $query->where($column, '=', $value);
        } else {
            $query->where(DB::raw($column), '=', $value); // todo serialize($value)
            unset($extra['is_encrypted']);
        }

        if (! is_null($excludeId) && $excludeId !== 'NULL') {
            $query->where($idColumn ?: 'id', '<>', $excludeId);
        }

        return $this->addConditions($query, $extra)->count();
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
            unset($extra['is_hashed']);
        }

        if (! empty($extra['is_encrypted'])) {
            $column = DB::raw($column);
            unset($extra['is_encrypted']);
        }

        $query = $this->table($collection)->whereIn($column, $values);

        return $this->addConditions($query, $extra)->distinct()->count($column);
    }
}
