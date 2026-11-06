<?php

namespace Webqamdev\EncryptableFields\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Webqamdev\EncryptableFields\Support\DB;

trait EncryptedSearchTrait
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  string[]|string  $columns
     * @return Builder<TModel>
     */
    protected function encryptedSearchLogic(Builder $query, array|string $columns, string $searchTerm): Builder
    {
        foreach (Arr::wrap($columns) as $column) {
            $query->orWhere(
                DB::getRawClause($column),
                'like',
                '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $searchTerm).'%',
            );
        }

        return $query;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  string[]|string  $columns
     * @return Builder<TModel>
     */
    protected function encryptedOrderLogic(Builder $query, array|string $columns, string $columnDirection): Builder
    {
        foreach (Arr::wrap($columns) as $column) {
            $query->orderBy(
                DB::getRawClause($column),
                $columnDirection,
            );
        }

        return $query;
    }
}
