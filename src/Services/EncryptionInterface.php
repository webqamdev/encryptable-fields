<?php

namespace Webqamdev\EncryptableFields\Services;

interface EncryptionInterface
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed $value
     * @return string
     */
    public static function encrypt($value): string;

    /**
     * Decrypt the given value.
     *
     * @param  string $value
     * @return mixed
     */
    public static function decrypt(string $value);
}
