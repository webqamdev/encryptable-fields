<?php

namespace Webqamdev\EncryptableFields\Services;

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
        return dbEncrypt($value);
    }

    /**
     * Decrypt the given value.
     *
     * @param string $value
     * @return mixed
     */
    public static function decrypt(string $value)
    {
        return dbDecrypt($value);
    }
}
