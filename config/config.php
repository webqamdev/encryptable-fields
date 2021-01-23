<?php

return [
    'key' => env('APP_DB_ENCRYPTION_KEY'),
    // Need to implement Webqamdev\EncryptableFields\Services\EncryptionInterface
    'encryption' => Webqamdev\EncryptableFields\Services\DatabaseEncryption::class,
    'hash_salt' => '--mDwt\k+PY,}vUJf2WeYUJ]yb(7A?>>bu7fGZrDpRUn#-kab',
];
