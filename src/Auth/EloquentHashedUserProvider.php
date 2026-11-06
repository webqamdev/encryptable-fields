<?php

namespace Webqamdev\EncryptableFields\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class EloquentHashedUserProvider extends EloquentUserProvider
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (
            $credentials === []
            || (
                count($credentials) === 1
                && array_key_exists('password', $credentials)
            )
        ) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newModelQuery();

        $model = new $this->model;

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            // @phpstan-ignore method.notFound (model uses the EncryptableFields trait)
            if ($model->isHashable($key)) {
                // @phpstan-ignore method.notFound
                $key = $model->getEncryptableArray()[$key];
                $value = dbHashValue($value);
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        // @phpstan-ignore return.type (configured model implements Authenticatable)
        return $query->first();
    }
}
