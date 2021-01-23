<?php

namespace Webqamdev\EncryptableFields\Rules\Unique;

use Illuminate\Validation\Rules\Unique;
use Webqamdev\EncryptableFields\Support\DB;

class Encrypted extends Unique
{
    public function __construct($table, $column = 'NULL')
    {
        parent::__construct($table, $column);

        $this->column = sprintf('"%s"', DB::getClause($column));
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return parent::__toString() . ',is_encrypted,1';
    }
}

