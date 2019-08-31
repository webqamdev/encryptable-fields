<?php

namespace Thomascombe\EncryptableFields\Models\Traits;

use Thomascombe\EncryptableFields\Services\EncryptionInterface;

/**
 * Trait EncryptedTrait
 *
 * @property array $attributes The attributes that should be encrypted in database.
 * @property array $encryptable The attributes that should be encrypted in database.
 *
 * @method bool hasGetMutator(string $key)
 * @method bool hasSetMutator(string $key)
 */
trait EncryptableFields
{
    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (!$this->hasSetMutator($key) && $this->isHashed($key)) {
            $this->setHashedAttribute($this->encryptable[$key], $value);
        }

        if (!$this->hasSetMutator($key) && $this->isEncryptable($key)) {
            return $this->setEncryptedAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function isEncryptable(string $key): bool
    {
        return in_array($key, array_keys($this->encryptable));
    }

    public function isHashed(string $key): bool
    {
        return $this->isEncryptable($key) && !empty($this->encryptable[$key]);
    }

    public function setEncryptedAttribute(string $key, $value)
    {
        $this->attributes[$key] = app()->make(EncryptionInterface::class)->encrypt($value);

        return $this;
    }

    public function setHashedAttribute(string $key, $value)
    {
        $this->attributes[$key] = self::hashValue($value);

        return $this;
    }

    public static function hashValue(?string $value): ?string
    {
        return $value ? sha1($value . config('encryptable-fields.encryption_salt')) : null;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string $key
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
        return empty($this->attributes[$key]) ? null : app()->make(EncryptionInterface::class)->decrypt($this->attributes[$key]);
    }
}
