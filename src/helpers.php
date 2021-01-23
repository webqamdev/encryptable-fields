<?php

if (!function_exists('hashValue')) {
    /**
     * Hash the given value.
     *
     * @param string $data
     * @param string $algorithm
     * @return string
     */
    function hashValue(string $data, string $algorithm = 'sha1'): string
    {
        return hash($algorithm, $data . config('encryptable-fields.hash_salt'));
    }
}
