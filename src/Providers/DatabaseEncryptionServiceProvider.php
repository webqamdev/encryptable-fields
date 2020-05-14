<?php

namespace Webqamdev\EncryptableFields\Providers;

use Illuminate\Encryption\EncryptionServiceProvider as BaseEncryptionServiceProvider;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

class DatabaseEncryptionServiceProvider extends BaseEncryptionServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDatabaseEncrypter();
    }

    /**
     * Register the encrypter.
     *
     * @return void
     */
    protected function registerDatabaseEncrypter(): void
    {
        $this->app->singleton('databaseEncrypter', function ($app) {
            $config = $app->make('config')->get('app');

            return new DatabaseEncrypter($this->parseKey($config));
        });
    }
}
