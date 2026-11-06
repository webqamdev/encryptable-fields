<?php

namespace Webqamdev\EncryptableFields\Services;

class Encryption implements EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public static function encrypt(mixed $value): string
    {
        return encrypt($value);
    }

    /**
     * Decrypt the given value.
     */
    public static function decrypt(string $value): mixed
    {
        return decrypt($value);
    }
}
