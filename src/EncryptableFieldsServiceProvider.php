<?php

namespace Webqamdev\EncryptableFields;

use Illuminate\Support\ServiceProvider;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;

class EncryptableFieldsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('encryptable-fields.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'encryptable-fields');
        $this->app->bind(EncryptionInterface::class, config('encryptable-fields.encryption'));

        $this->loadHelpers();
    }

    /**
     * Load the Backpack helper methods, for convenience.
     */
    public function loadHelpers(): self
    {
        require_once __DIR__ . '/helpers.php';

        return $this;
    }
}
