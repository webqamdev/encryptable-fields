<?php

namespace Webqamdev\EncryptableFields\Models\Interfaces;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface Encryptable
 *
 * @method static Builder whereHashed(string $key, mixed $value)
 * @method static Builder whereOrHashed(string $key, mixed $value)
 * @method static Builder whereHashedNot(string $key, mixed $value)
 * @method static Builder whereEncrypted(string $key, mixed $value)
 * @method static Builder whereOrEncrypted(string $key, mixed $value)
 * @method static Builder whereEncryptedNot(string $key, mixed $value)
 */
interface Encryptable
{
    public function isHashable(string $key): bool;

    public function isEncryptable(string $key): bool;

    public function getEncryptableArray(): array;

    public function setHashedAttribute(string $key, $value): self;

    public function setEncryptedAttribute(string $key, $value): self;

    public function getEncryptedAttribute(string $key): mixed;

    public function scopeWhereHashed(Builder $query, string $key, string $value): void;

    public function scopeOrWhereHashed(Builder $query, string $key, string $value): void;

    public function scopeWhereHashedNot(Builder $query, string $key, string $value): void;

    public function scopeWhereEncrypted(Builder $query, string $key, string $value): void;

    public function scopeOrWhereEncrypted(Builder $query, string $key, string $value): void;

    public function scopeWhereEncryptedNot(Builder $query, string $key, string $value): void;
}
