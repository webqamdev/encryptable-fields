<?php

namespace Webqamdev\EncryptableFields\Http\Controllers\Admin\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
                DB::raw($this->formatClause($column)),
                'like',
                '%' . $searchTerm . '%'
            );
        }

        return $query;
    }

    protected function formatClause(string $column): string
    {
        $format = 'convert(aes_decrypt(from_base64(json_value(from_base64(%s), "$.value")), "%s") USING utf8mb4)';
        $escapedColumn = sprintf('`%s`', str_replace('.', '`.`', $column));
        $key = app()->get('databaseEncrypter')->getKey();

        return sprintf($format, $escapedColumn, $key);
    }

    protected function encryptedOrderLogic(Builder $query, $columns, string $columnDirection): Builder
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $query->orderBy(
                DB::raw($this->formatClause($column)),
                $columnDirection
            );
        }

        return $query;
    }
}
