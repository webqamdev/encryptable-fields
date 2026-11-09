<?php

namespace Webqamdev\EncryptableFields\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Webqamdev\EncryptableFields\Casts\Encrypted;
use Webqamdev\EncryptableFields\Exceptions\NotHashedFieldException;
use Webqamdev\EncryptableFields\Services\DatabaseEncryption;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;
use Webqamdev\EncryptableFields\Support\DB;

/**
 * Registers an Encrypted cast for each attribute listed in the $encryptable property.
 *
 * @property array<int|string, string> $encryptable The attributes that should be encrypted in database.
 */
trait EncryptableFields
{
    public function initializeEncryptableFields(): void
    {
        $this->mergeCasts(array_map(
            fn (?string $hashKey): string => $hashKey === null ? Encrypted::class : Encrypted::class.':'.$hashKey,
            $this->getEncryptableArray(),
        ));
    }

    /**
     * Check if a field is hashable
     */
    public function isHashable(string $key): bool
    {
        return $this->isEncryptable($key) && ! empty($this->getEncryptableArray()[$key]);
    }

    /**
     * Check if a field is encryptable
     */
    public function isEncryptable(string $key): bool
    {
        return array_key_exists($key, $this->getEncryptableArray());
    }

    /**
     * @return array<string, string|null> Encrypted attributes, with their hash attribute
     */
    public function getEncryptableArray(): array
    {
        $encryptable = [];

        foreach ($this->encryptable as $key => $value) {
            if (is_int($key)) {
                $encryptable[$value] = null;
            } else {
                $encryptable[$key] = $value;
            }
        }

        return $encryptable;
    }

    /**
     * A scope to search for a value on its hash column
     *
     * @param  Builder<static>  $query  The QueryBuilder
     * @param  string  $key  The column name
     * @param  string  $value  The non-encrypted value to search for
     *
     * @throws NotHashedFieldException
     */
    #[Scope]
    protected function whereHashed(Builder $query, string $key, string $value): void
    {
        if (! $this->isHashable($key)) {
            throw new NotHashedFieldException(sprintf('%s is not hashable', $key));
        }

        $query->where($this->getEncryptableArray()[$key], dbHashValue($value));
    }

    /**
     * A scope to search for a value on its decrypted value with DatabaseEncryption, on its hash column otherwise.
     * Columns which are not encrypted are matched as is with DatabaseEncryption.
     *
     * @param  Builder<static>  $query  The QueryBuilder
     * @param  string  $key  The column name
     * @param  string  $value  The non-encrypted value to search for
     *
     * @throws NotHashedFieldException Without DatabaseEncryption, when the column is not hashed
     */
    #[Scope]
    protected function whereEncrypted(Builder $query, string $key, string $value): void
    {
        if (! app(EncryptionInterface::class) instanceof DatabaseEncryption) {
            $this->whereHashed($query, $key, $value);

            return;
        }

        $qualified = $query->qualifyColumn($key);

        $query->where(
            $this->isEncryptable($key) ? DB::getRawClause($qualified, $query->getQuery()->getGrammar()) : $qualified,
            $value,
        );
    }

    /**
     * A scope to search a term in decrypted values with LIKE, in any of the given columns (OR).
     * Columns which are not encrypted are matched as is. Requires searchable encrypted values.
     *
     * @param  Builder<static>  $query  The QueryBuilder
     * @param  string  $search  The non-encrypted term to search for
     * @param  string[]|string  $columns  The column names
     */
    #[Scope]
    protected function searchEncrypted(Builder $query, string $search, array|string $columns): void
    {
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
        $grammar = $query->getQuery()->getGrammar();

        $query->where(function (Builder $query) use ($columns, $grammar, $like): void {
            foreach (Arr::wrap($columns) as $column) {
                $qualified = $query->qualifyColumn($column);
                $query->orWhere($this->isEncryptable($column) ? DB::getRawClause($qualified, $grammar) : $qualified, 'like', $like);
            }
        });
    }

    /**
     * When serializing, Laravel applies class casts instead of accessors: apply accessors of encrypted attributes.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach (array_keys($this->getEncryptableArray()) as $key) {
            if (array_key_exists($key, $attributes) && ($this->hasGetMutator($key) || $this->hasAttributeGetMutator($key))) {
                $attributes[$key] = $this->getAttribute($key);
            }
        }

        return $attributes;
    }

    /**
     * Casts are skipped when an accessor exists: decrypt so that accessors receive the plain value.
     */
    protected function mutateAttribute($key, $value): mixed
    {
        return parent::mutateAttribute($key, $this->isEncryptable($key) ? Encrypted::decrypt($value) : $value);
    }

    /**
     * Same as mutateAttribute(), for accessors using the Attribute return type.
     */
    protected function mutateAttributeMarkedAttribute($key, $value): mixed
    {
        if (! array_key_exists($key, $this->attributeCastCache) && $this->isEncryptable($key)) {
            $value = Encrypted::decrypt($value);
        }

        return parent::mutateAttributeMarkedAttribute($key, $value);
    }
}
