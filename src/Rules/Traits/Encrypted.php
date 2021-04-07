<?php

namespace Webqamdev\EncryptableFields\Rules\Traits;

use Webqamdev\EncryptableFields\Support\DB;

trait Encrypted
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
