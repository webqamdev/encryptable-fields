<?php

namespace Webqamdev\EncryptableFields\Rules\Concerns;

use Webqamdev\EncryptableFields\Support\DB;

trait Encrypted
{
    public function __construct(string $table, string $column)
    {
        parent::__construct($table, $column);

        // Same connection resolution as the validator: "connection.table"
        $connection = str_contains($this->table, '.') ? strstr($this->table, '.', true) : null;

        $this->column = sprintf(
            '"%s"',
            DB::getClause($column, app('db')->connection($connection)->getQueryGrammar()),
        );
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        return parent::__toString().',is_encrypted,1';
    }
}
