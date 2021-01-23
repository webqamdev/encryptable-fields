<?php

namespace Webqamdev\EncryptableFields\Http\Controllers\Admin\Traits;

use Illuminate\Database\Eloquent\Builder;
use Webqamdev\EncryptableFields\Support\DB;

trait EncryptedSearchTrait
{
    /**
     * @param Builder $query
     * @param array|string $columns
     * @param string $searchTerm
     * @return Builder
     */
    protected function encryptedSearchLogic(Builder $query, $columns, string $searchTerm): Builder
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $query->orWhere(
                DB::getRawClause($column),
                'like',
                '%' . $searchTerm . '%'
            );
        }

        return $query;
    }

    protected function encryptedOrderLogic(Builder $query, $columns, string $columnDirection): Builder
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $query->orderBy(
                DB::getRawClause($column),
                $columnDirection
            );
        }

        return $query;
    }
}
