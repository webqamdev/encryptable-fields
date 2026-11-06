<?php

namespace Webqamdev\EncryptableFields\Services;

interface EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public function encrypt(mixed $value): string;

    /**
     * Decrypt the given value.
     */
    public function decrypt(string $value): mixed;
}
