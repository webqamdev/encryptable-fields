<?php

namespace Webqamdev\EncryptableFields\Services;

use Illuminate\Contracts\Encryption\DecryptException;

interface EncryptionInterface
{
    /**
     * Encrypt the given value.
     */
    public function encrypt(mixed $value): string;

    /**
     * Decrypt the given value.
     *
     * @throws DecryptException
     */
    public function decrypt(string $value): mixed;
}
