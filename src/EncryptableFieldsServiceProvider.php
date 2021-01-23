<?php

namespace Webqamdev\EncryptableFields;

use Illuminate\Support\ServiceProvider;
use Webqamdev\EncryptableFields\Console\KeyGenerateCommand;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;

class EncryptableFieldsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfiguration();
        }
    }

    protected function publishConfiguration(): self
    {
        $this->publishes(
            [
                __DIR__ . '/../config/config.php' => config_path('encryptable-fields.php'),
            ],
            'config'
        );

        return $this;
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this
            ->mergeConfiguration()
            ->registerBindings()
            ->registerCommands()
            ->registerDatabaseEncrypter()
            ->loadHelpers();
    }

    /**
     * Load the Backpack helper methods, for convenience.
     */
    public function loadHelpers(): self
    {
        require_once __DIR__ . '/helpers.php';

        return $this;
    }

    protected function registerDatabaseEncrypter(): self
    {
        $this->app->singleton('databaseEncrypter', function ($app) {
            $config = $app->make('config')->get('encryptable-fields');

            return new DatabaseEncrypter($this->key($config));
        });

        return $this;
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param  array  $config
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function key(array $config)
    {
        return tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new RuntimeException(
                    'No application encryption key has been specified.'
                );
            }
        });
    }

    protected function registerCommands(): self
    {
        $this->commands([
            KeyGenerateCommand::class,
        ]);

        return $this;
    }

    protected function registerBindings(): self
    {
        $this->app->bind(EncryptionInterface::class, config('encryptable-fields.encryption'));

        return $this;
    }

    protected function mergeConfiguration(): self
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'encryptable-fields');

        return $this;
    }
}
