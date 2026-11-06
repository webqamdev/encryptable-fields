<?php

namespace Webqamdev\EncryptableFields\Services;

class DatabaseEncryption implements EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public static function encrypt(mixed $value): string
    {
        return dbEncrypt($value);
    }

    /**
     * Decrypt the given value.
     */
    public static function decrypt(string $value): mixed
    {
        return dbDecrypt($value);
    }
}
