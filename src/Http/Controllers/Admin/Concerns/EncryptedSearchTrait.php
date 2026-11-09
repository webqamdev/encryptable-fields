<?php

namespace Webqamdev\EncryptableFields\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Webqamdev\EncryptableFields\Support\DB;

trait EncryptedSearchTrait
{
    /**
     * Adds an OR condition, as Backpack groups the search logics of all columns. The model must use EncryptableFields.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  string[]|string  $columns
     * @return Builder<TModel>
     */
    protected function encryptedSearchLogic(Builder $query, array|string $columns, string $searchTerm): Builder
    {
        return $query->orWhere(fn (Builder $query) => $query->scopes(['searchEncrypted' => [$searchTerm, $columns]]));
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
                DB::getRawClause($column, $query->getQuery()->getGrammar()),
                $columnDirection,
            );
        }

        return $query;
    }
}
