<?php

namespace Webqamdev\EncryptableFields\Models\Interfaces;

interface Encryptable
{
    public function isHashable(string $key): bool;

    public function isEncryptable(string $key): bool;

    public function getEncryptableArray(): array;

    public function setHashedAttribute(string $key, $value): self;

    public function setEncryptedAttribute(string $key, $value): self;

    public function getEncryptedAttribute(string $key): mixed;
}
