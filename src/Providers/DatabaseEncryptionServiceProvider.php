<?php

namespace Webqamdev\EncryptableFields\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Encryption\EncryptionServiceProvider as BaseEncryptionServiceProvider;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Support\DB;

class DatabaseEncryptionServiceProvider extends BaseEncryptionServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerDatabaseEncrypter();
    }

    public function boot(): void
    {
        foreach ($this->app['db']->getConnections() as $connection) {
            DB::watch($connection);
        }

        $this->app['events']->listen(
            ConnectionEstablished::class,
            static fn (ConnectionEstablished $event) => DB::watch($event->connection),
        );
    }

    /**
     * Register the encrypter.
     */
    protected function registerDatabaseEncrypter(): void
    {
        $this->app->singleton('databaseEncrypter', function ($app): DatabaseEncrypter {
            $config = $app->make('config')->get('encryptable-fields');

            return new DatabaseEncrypter($this->parseKey($config), $config['cipher']);
        });
    }
}
