<?php

namespace Webqamdev\EncryptableFields\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Webqamdev\EncryptableFields\Casts\Encrypted;
use Webqamdev\EncryptableFields\Exceptions\NotHashedFieldException;

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
     * checked if field is hashable
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
            is_int($key) ? $encryptable[$value] = null : $encryptable[$key] = $value;
        }

        return $encryptable;
    }

    /**
     * A scope to search for encrypted values in the database
     *
     * @param  Builder<static>  $query  The QueryBuilder
     * @param  string  $key  The column name
     * @param  string  $value  The non-encrypted value to search for
     *
     * @throws NotHashedFieldException
     */
    public function scopeWhereEncrypted(Builder $query, string $key, string $value): void
    {
        if (! $this->isHashable($key)) {
            throw new NotHashedFieldException(sprintf('%s is not hashable', $key));
        }

        $query->where($this->getEncryptableArray()[$key], dbHashValue($value));
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
