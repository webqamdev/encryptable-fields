<?php

namespace Webqamdev\EncryptableFields\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * For a Filament resource whose model uses EncryptableFields: global search decrypts encrypted attributes.
 * Searched attributes are the ones of getGloballySearchableAttributes(), flattened (no relationship).
 */
trait SearchesEncryptedAttributes
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    protected static function applyGlobalSearchAttributeConstraints(Builder $query, string $search): void
    {
        $query->scopes(['searchEncrypted' => [$search, Arr::flatten(static::getGloballySearchableAttributes())]]);
    }
}
