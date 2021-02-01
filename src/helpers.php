<?php

if (!function_exists('dbEncrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @param bool $serialize
     * @return string
     */
    function dbEncrypt($value, bool $serialize = true): string
    {
        return app('databaseEncrypter')->encrypt($value, $serialize);
    }
}

if (!function_exists('dbDecrypt')) {
    /**
     * Decrypt the given value.
     *
     * @param string $value
     * @param bool $unserialize
     * @return mixed
     */
    function dbDecrypt(string $value, bool $unserialize = true)
    {
        return app('databaseEncrypter')->decrypt($value, $unserialize);
    }
}
