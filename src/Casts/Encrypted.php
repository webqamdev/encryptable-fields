<?php

namespace Webqamdev\EncryptableFields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;

/**
 * Encrypts an attribute and, when a hash column is given, stores its HMAC in that column.
 *
 * @implements CastsAttributes<mixed, mixed>
 */
readonly class Encrypted implements CastsAttributes
{
    public function __construct(private ?string $hashColumn = null) {}

    public static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    public static function decrypt(mixed $value): mixed
    {
        return self::isEmpty($value) ? null : app(EncryptionInterface::class)->decrypt($value);
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return self::decrypt($value);
    }

    /**
     * @return array<string, string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $empty = self::isEmpty($value);

        $result = [$key => $empty ? null : app(EncryptionInterface::class)->encrypt($value)];

        if ($this->hashColumn !== null) {
            $result[$this->hashColumn] = $empty ? null : dbHashValue((string) $value);
        }

        return $result;
    }
}
