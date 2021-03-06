<?php

namespace Webqamdev\EncryptableFields;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Webqamdev\EncryptableFields\Auth\EloquentHashedUserProvider;
use Webqamdev\EncryptableFields\Console\KeyGenerateCommand;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;
use Webqamdev\EncryptableFields\Validation\DatabasePresenceVerifier;

class EncryptableFieldsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfiguration();
        }

        $this->bootAuthProviders();
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

    protected function bootAuthProviders(): self
    {
        Auth::provider('eloquent-hashed', function ($app, array $config) {
            return new EloquentHashedUserProvider($this->app['hash'], $config['model']);
        });

        return $this;
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this
            ->registerPresenceVerifier()
            ->mergeConfiguration()
            ->registerBindings()
            ->registerCommands()
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

    protected function registerPresenceVerifier(): self
    {
        $this->app->extend('validation.presence', function ($presenceVerifier, $app) {
            return new DatabasePresenceVerifier($app['db']);
        });

        return $this;
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param array $config
     * @return string
     * @throws RuntimeException
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
}
