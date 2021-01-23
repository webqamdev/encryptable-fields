<?php

namespace Webqamdev\EncryptableFields\Validation;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\DatabasePresenceVerifier as BaseDatabasePresenceVerifier;

class DatabasePresenceVerifier extends BaseDatabasePresenceVerifier
{
    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param string $collection
     * @param string $column
     * @param string $value
     * @param int|null $excludeId
     * @param string|null $idColumn
     * @param array $extra
     * @return int
     */
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = [])
    {
        $query = $this->table($collection);

        if (empty($extra['is_encrypted'])) {
            if (!empty($extra['is_hashed'])) {
                $value = hashValue($value);
                unset($extra['is_hashed']);
            }

            $query->where($column, '=', $value);
        } else {
            $query->where(DB::raw($column), '=', $value); // todo serialize($value)
            unset($extra['is_encrypted']);
        }

        if (!is_null($excludeId) && $excludeId !== 'NULL') {
            $query->where($idColumn ?: 'id', '<>', $excludeId);
        }

        return $this->addConditions($query, $extra)->count();
    }
}
