<?php

namespace Webqamdev\EncryptableFields\Services;

interface EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public static function encrypt(mixed $value): string;

    /**
     * Decrypt the given value.
     */
    public static function decrypt(string $value): mixed;
}
