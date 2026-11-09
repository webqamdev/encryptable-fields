<?php

namespace Webqamdev\EncryptableFields\Services;

class DatabaseEncryption implements EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public function encrypt(mixed $value): string
    {
        return dbEncrypt($value);
    }

    /**
     * Decrypt the given value.
     */
    public function decrypt(string $value): mixed
    {
        return dbDecrypt($value);
    }
}
