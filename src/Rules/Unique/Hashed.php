<?php

namespace Webqamdev\EncryptableFields\Rules\Unique;

use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class Hashed extends Unique
{
    /**
     * Create a new rule instance.
     *
     * @param string $table
     * @param string $column
     * @param bool $addHashSuffixIfNeeded
     */
    public function __construct($table, $column = 'NULL', bool $addHashSuffixIfNeeded = true)
    {
        if (!Str::endsWith($column, '_hash')) {
            $column .= '_hash';
        }

        parent::__construct($table, $column);
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return parent::__toString() . ',is_hashed,1';
    }
}

