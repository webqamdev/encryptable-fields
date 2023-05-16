<?php

namespace Webqamdev\EncryptableFields\Models\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Webqamdev\EncryptableFields\Exceptions\NotHashedFieldException;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;

/**
 * Trait EncryptedTrait
 *
 * @property array $attributes The attributes that should be encrypted in database.
 * @property array $encryptable The attributes that should be encrypted in database.
 * @property array $encryptableArray Magic mutator
 *
 * @method bool hasGetMutator(string $key)
 * @method bool hasSetMutator(string $key)
 */
trait EncryptableFields
{
    /**
     * Check if a field is encryptable
     *
     * @param string $key
     * @return bool
     */
    public function isEncryptable(string $key): bool
    {
        return array_key_exists($key, $this->getEncryptableArray());
    }

    /**
     * checked if field is hashable
     *
     * @param string $key
     * @return bool
     */
    public function isHashable(string $key): bool
    {
        return $this->isEncryptable($key) && !empty($this->getEncryptableArray()[$key]);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     * @throws BindingResolutionException
     */
    public function setAttribute($key, $value)
    {
        $hasSetMutator = $this->hasSetMutator($key);

        if (!$hasSetMutator && method_exists($this, 'hasAttributeSetMutator')) {
            $hasSetMutator = $this->hasAttributeSetMutator($key);
        }

        if (!$hasSetMutator && $this->isHashable($key)) {
            if (!empty($value)) {
                $this->setHashedAttribute($this->getEncryptableArray()[$key], $value);
            } else {
                $this->attributes[$this->getEncryptableArray()[$key]] = null;
            }
        }

        if (!$hasSetMutator && $this->isEncryptable($key)) {
            if (!empty($value)) {
                return $this->setEncryptedAttribute($key, $value);
            } else {
                $this->attributes[$key] = null;
                return $this;
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Save encryptable attribute
     *
     * @param string $key
     * @param $value
     * @return $this
     * @throws BindingResolutionException
     */
    public function setEncryptedAttribute(string $key, $value)
    {
        $this->attributes[$key] = app()->make(EncryptionInterface::class)->encrypt($value);

        return $this;
    }

    /**
     * Save hashable attribute
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setHashedAttribute(string $key, $value)
    {
        $this->attributes[$key] = dbHashValue($value);

        return $this;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param string $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if ($this->isEncryptable($key)) {
            return $this->getEncryptedAttribute($key);
        }

        return parent::getAttributeFromArray($key);
    }

    public function getEncryptedAttribute(string $key)
    {
        return empty($this->attributes[$key]) ? null : $this->getDecryptValue($key);
    }

    /**
     * A scope to search for encrypted values in the database
     * @param Builder $query The QueryBuilder
     * @param string $key The column name
     * @param string $value The non-encrypted value to search for
     * @return void
     * @throws NotHashedFieldException
     */
    public function scopeWhereEncrypted(Builder $query, string $key, string $value): void
    {
        if (!$this->isHashable($key)) {
            throw new NotHashedFieldException(sprintf('%s is not hashable', $key));
        }

        $query->where($this->getEncryptableArray()[$key], dbHashValue($value));
    }

    public function getEncryptableArray(): array
    {
        return collect($this->encryptable)
            ->mapWithKeys(function ($value, $key) {
                if (is_int($key)) {
                    return [$value => null];
                }
                return [$key => $value];
            })
            ->toArray();
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getEncryptableArray() as $key => $value) {
            if (isset($attributes[$key])) {
                $attributes[$key] = $this->getAttribute($key);
            }
        }

        return $attributes;
    }

    protected function getDecryptValue(string $key)
    {
        return app()->make(EncryptionInterface::class)->decrypt($this->attributes[$key]);
    }

    /**
     * Get the value of an "Attribute" return type marked attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function mutateAttributeMarkedAttribute($key, $value)
    {
        if (!array_key_exists($key, $this->attributeCastCache) && $this->isEncryptable($key)) {
            $value = $this->getDecryptValue($key);
        }

        return parent::mutateAttributeMarkedAttribute($key, $value);
    }
}
