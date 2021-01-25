<?php

return [
    'key' => env('APP_DB_ENCRYPTION_KEY'),
    // Need to implement EncryptionInterface
    'encryption' => \Webqamdev\EncryptableFields\Services\Encryption::class,
    'hash_salt' => '--mDwt\k+PY,}vUJf2WeYUJ]yb(7A?>>bu7fGZrDpRUn#-kab'
];
