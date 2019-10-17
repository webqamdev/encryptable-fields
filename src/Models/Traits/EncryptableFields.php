<?php

namespace Thomascombe\EncryptableFields\Models\Traits;

use Illuminate\Database\Query\Builder;
use Thomascombe\EncryptableFields\Exceptions\NotHashedFieldException;
use Thomascombe\EncryptableFields\Services\EncryptionInterface;

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
    private function isEncryptable(string $key): bool
    {
        return array_key_exists($key, $this->getEncryptableArray());
    }

    /**
     * checked if field is hashable
     *
     * @param string $key
     * @return bool
     */
    private function isHashable(string $key): bool
    {
        return $this->isEncryptable($key) && !empty($this->getEncryptableArray()[$key]);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setAttribute($key, $value)
    {
        if (!$this->hasSetMutator($key) && $this->isHashable($key)) {
            $this->setHashedAttribute($this->getEncryptableArray()[$key], $value);
        }

        if (!$this->hasSetMutator($key) && $this->isEncryptable($key)) {
            return $this->setEncryptedAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Save encryptable attribute
     *
     * @param string $key
     * @param $value
     * @return $this
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
        $this->attributes[$key] = self::hashValue($value);

        return $this;
    }

    /**
     * Hash a value to save in field or to SQL request
     *
     * @param string|null $value
     * @return string|null
     */
    public static function hashValue(?string $value): ?string
    {
        return $value ? sha1($value . config('encryptable-fields.hash_salt')) : null;
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
        return empty($this->attributes[$key])
            ? null
            : app()->make(EncryptionInterface::class)->decrypt($this->attributes[$key]);
    }

    /**
     * A scope to search for encrypted values in the database
     * @param Builder $query The QueryBuilder
     * @param string $key The column name
     * @param string $value The non-encrypted value to search for
     * @return void
     * @throws NotHashedFieldException
     */
    public function scopeWhereEncrypted($query, $key, $value)
    {
        if (!$this->isHashable($key)) {
            throw new NotHashedFieldException(sprintf('%s is not hashable', $key));
        }

        $query->where($this->getEncryptableArray()[$key], self::hashValue($value));
    }

    protected function getEncryptableArray()
    {
        return collect($this->encryptable)->mapWithKeys(function ($value, $key) {
            if(is_int($key)) {
                return [$value => null];
            }
            return [$key => $value];
        })->toArray();
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
}
