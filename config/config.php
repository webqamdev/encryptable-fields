<?php

use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Services\Encryption;

return [
    // Database encryption key, generated with `php artisan encryptable-fields:key-generate`
    'key' => env('APP_DB_ENCRYPTION_KEY'),

    // Database encryption cipher, must be supported by your database server:
    // - aes-128-ecb (DatabaseEncrypter::CIPHER_AES_128_ECB): MySQL 5.6+ / MariaDB 10.x+ (identical values give identical ciphertexts)
    // - aes-256-cbc (DatabaseEncrypter::CIPHER_AES_256_CBC): MySQL 5.6.17+ / MariaDB 11.2+ (random IV, recommended when available)
    'cipher' => env('APP_DB_ENCRYPTION_CIPHER', DatabaseEncrypter::CIPHER_AES_128_ECB),

    // Need to implement EncryptionInterface
    'encryption' => Encryption::class,

    // HMAC key used to hash values, generated with `php artisan encryptable-fields:key-generate --hash`
    'hash_key' => env('APP_DB_HASH_KEY'),

    // v3 database encryption key, only needed by `php artisan encryptable-fields:migrate-legacy`
    'legacy_key' => env('APP_DB_ENCRYPTION_LEGACY_KEY'),
];
