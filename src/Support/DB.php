<?php

namespace Webqamdev\EncryptableFields\Support;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB as Facade;

class DB
{
    public static function getClause(string $column): string
    {
        return sprintf(
            'convert(aes_decrypt(from_base64(json_value(from_base64(%s), \'$.value\')), \'%s\') USING utf8mb4)',
            sprintf('`%s`', str_replace('.', '`.`', $column)),
            app()->get('databaseEncrypter')->getKey()
        );
    }

    public static function getRawClause(string $column): Expression
    {
        return Facade::raw(
            self::getClause($column)
        );
    }
}
