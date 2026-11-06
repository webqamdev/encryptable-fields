<?php

namespace Webqamdev\EncryptableFields\Rules\Concerns;

use Illuminate\Support\Str;

trait Hashed
{
    /**
     * Create a new rule instance.
     */
    public function __construct(string $table, string $column, bool $addHashSuffixIfNeeded = true)
    {
        if ($addHashSuffixIfNeeded && ! Str::endsWith($column, '_hash')) {
            $column .= '_hash';
        }

        parent::__construct($table, $column);
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        return parent::__toString().',is_hashed,1';
    }
}
