<?php

use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

if (! function_exists('dbEncrypt')) {
    /**
     * Encrypt the given value.
     */
    function dbEncrypt(mixed $value, bool $serialize = false): string
    {
        return app(DatabaseEncrypter::class)->encrypt($value, $serialize);
    }
}

if (! function_exists('dbDecrypt')) {
    /**
     * Decrypt the given value.
     */
    function dbDecrypt(string $value, bool $unserialize = false): mixed
    {
        return app(DatabaseEncrypter::class)->decrypt($value, $unserialize);
    }
}

if (! function_exists('dbHashValue')) {
    /**
     * Hash the given value with HMAC-SHA256.
     *
     * @throws RuntimeException
     */
    function dbHashValue(string $data): string
    {
        $key = config('encryptable-fields.hash_key');

        if (empty($key)) {
            throw new RuntimeException('No hash key has been specified (APP_DB_HASH_KEY).');
        }

        return hash_hmac('sha256', $data, $key);
    }
}
