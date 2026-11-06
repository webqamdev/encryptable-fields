<?php

namespace Webqamdev\EncryptableFields\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use SensitiveParameter;

class EloquentHashedUserProvider extends EloquentUserProvider
{
    /**
     * Replaces hashable credentials with their hash column and value, then lets Laravel query them.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function retrieveByCredentials(#[SensitiveParameter] array $credentials): ?Authenticatable
    {
        $model = $this->createModel();
        $hashed = [];

        foreach ($credentials as $key => $value) {
            // @phpstan-ignore method.notFound (model uses the EncryptableFields trait)
            if (! str_contains($key, 'password') && $model->isHashable($key)) {
                $hash = static fn (mixed $value): string => dbHashValue((string) $value);

                // @phpstan-ignore method.notFound
                $key = $model->getEncryptableArray()[$key];
                $value = match (true) {
                    $value instanceof Arrayable => array_map($hash, $value->toArray()),
                    is_array($value) => array_map($hash, $value),
                    default => $hash($value),
                };
            }

            $hashed[$key] = $value;
        }

        return parent::retrieveByCredentials($hashed);
    }
}
