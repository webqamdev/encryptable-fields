<?php

namespace Thomascombe\EncryptableFields\Services;

class Encryption implements EncryptionInterface
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed $value
     * @return string
     */
    public static function encrypt($value): string
    {
        return encrypt($value);
    }

    /**
     * Decrypt the given value.
     *
     * @param  string $value
     * @return mixed
     */
    public static function decrypt(string $value)
    {
        return decrypt($value);
    }
}
