<?php

namespace Webqamdev\EncryptableFields\Services;

class Encryption implements EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public function encrypt(mixed $value): string
    {
        return encrypt($value);
    }

    /**
     * Decrypt the given value.
     */
    public function decrypt(string $value): mixed
    {
        return decrypt($value);
    }
}
