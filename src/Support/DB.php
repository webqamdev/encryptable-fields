<?php

namespace Webqamdev\EncryptableFields\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression as RawExpression;
use PDO;
use WeakMap;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

class DB
{
    /**
     * MySQL session variable holding the key, so that it never appears in SQL statements.
     */
    public const string KEY_VARIABLE = '@encryptable_fields_key';

    /** @var WeakMap<Connection, true>|null */
    private static ?WeakMap $watchedConnections = null;

    /** @var WeakMap<PDO, true>|null */
    private static ?WeakMap $initializedPdos = null;

    public static function getClause(string $column): string
    {
        $payload = sprintf(
            'convert(from_base64(%s) using utf8mb4)',
            sprintf('`%s`', str_replace('.', '`.`', $column)),
        );

        $arguments = sprintf("from_base64(json_value(%s, '$.value')), %s", $payload, self::KEY_VARIABLE);

        if (! app(DatabaseEncrypter::class)->isEcb()) {
            $arguments .= sprintf(", from_base64(json_value(%s, '$.iv'))", $payload);
        }

        return sprintf('convert(aes_decrypt(%s) USING utf8mb4)', $arguments);
    }

    public static function getRawClause(string $column): Expression
    {
        return new RawExpression(self::getClause($column));
    }

    /**
     * Set the key session variable before the first query using it, on each PDO of the connection.
     */
    public static function watch(Connection $connection): void
    {
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        self::$watchedConnections ??= new WeakMap;

        if (isset(self::$watchedConnections[$connection])) {
            return;
        }

        self::$watchedConnections[$connection] = true;

        $connection->beforeExecuting(static function (string $query, array $bindings, Connection $connection): void {
            if (str_contains($query, self::KEY_VARIABLE)) {
                self::initializeSession($connection);
            }
        });
    }

    private static function initializeSession(Connection $connection): void
    {
        self::$initializedPdos ??= new WeakMap;

        $encrypter = app(DatabaseEncrypter::class);

        $statement = sprintf('set %s = from_base64(?)', self::KEY_VARIABLE);

        if (! $encrypter->isEcb()) {
            $statement .= sprintf(", block_encryption_mode = '%s'", DatabaseEncrypter::CIPHER_AES_256_CBC);
        }

        // ponytail: opens the write connection even for read-only queries, split per query type if it matters
        foreach ([$connection->getPdo(), $connection->getReadPdo()] as $pdo) {
            if (isset(self::$initializedPdos[$pdo])) {
                continue;
            }

            // Raw PDO call: keeps the key out of Laravel query log, events and exceptions
            $pdo->prepare($statement)->execute([base64_encode($encrypter->getKey())]);

            self::$initializedPdos[$pdo] = true;
        }
    }
}
