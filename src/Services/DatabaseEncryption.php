<?php

namespace Webqamdev\EncryptableFields\Services;

use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

class DatabaseEncryption implements EncryptionInterface
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @return string
     */
    public static function encrypt($value): string
    {
        return self::getService()->encrypt($value);
    }

    protected static function getService(): DatabaseEncrypter
    {
        return app()->get('databaseEncrypter');
    }

    /**
     * Decrypt the given value.
     *
     * @param string $value
     * @return mixed
     */
    public static function decrypt(string $value)
    {
        return self::getService()->decrypt($value);
    }
}
