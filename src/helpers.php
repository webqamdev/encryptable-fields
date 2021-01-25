<?php

if (!function_exists('dbHashValue')) {
    /**
     * Hash the given value.
     *
     * @param string $data
     * @param string $algorithm
     * @return string
     */
    function dbHashValue(string $data, string $algorithm = 'sha1'): string
    {
        return hash($algorithm, $data . config('encryptable-fields.hash_salt'));
    }
}
