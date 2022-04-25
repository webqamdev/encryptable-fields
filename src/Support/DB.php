<?php

namespace Webqamdev\EncryptableFields\Support;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB as Facade;

class DB
{
    public static function getClause(string $column): string
    {
        return sprintf(
            'aes_decrypt(from_base64(json_unquote(json_extract(convert(from_base64(%s) USING utf8mb4), \'$.value\'))), \'%s\')',
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
