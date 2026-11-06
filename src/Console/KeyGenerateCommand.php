<?php

namespace Webqamdev\EncryptableFields\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Console\KeyGenerateCommand as BaseCommand;
use Illuminate\Support\Facades\File;
use Random\RandomException;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

class KeyGenerateCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryptable-fields:key-generate
                    {--hash : Generate the hash key (APP_DB_HASH_KEY) instead of the database encryption key}
                    {--show : Display the key instead of modifying files}
                    {--force : Overwrite an existing key without confirmation}';

    protected $description = 'Set the database encryption key (APP_DB_ENCRYPTION_KEY) or the hash key (APP_DB_HASH_KEY)';

    /**
     * Execute the console command.
     *
     * @throws RandomException
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        if (! $this->hasEnvironmentVariable()) {
            $this->error(
                sprintf('Missing environment variable. Please add %s= to your .env file.', $this->envVariableName()),
            );

            return self::FAILURE;
        }

        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            $this->line('<comment>'.$key.'</comment>');

            return self::SUCCESS;
        }

        if (! $this->setKeyInEnvironmentFile($key)) {
            return self::SUCCESS;
        }

        $this->laravel['config'][$this->configKey()] = $key;

        $this->info(sprintf('%s set successfully.', $this->envVariableName()));

        return self::SUCCESS;
    }

    protected function envVariableName(): string
    {
        return $this->option('hash') ? 'APP_DB_HASH_KEY' : 'APP_DB_ENCRYPTION_KEY';
    }

    protected function configKey(): string
    {
        return $this->option('hash') ? 'encryptable-fields.hash_key' : 'encryptable-fields.key';
    }

    /**
     * @throws FileNotFoundException
     */
    protected function hasEnvironmentVariable(): bool
    {
        return str_contains(
            File::get($this->laravel->environmentFilePath()),
            $this->envVariableName().'=',
        );
    }

    /**
     * Generate a random key, base64 encoded.
     *
     * @throws RandomException
     */
    protected function generateRandomKey(): string
    {
        $key = $this->option('hash')
            ? random_bytes(32)
            : DatabaseEncrypter::generateKey($this->laravel['config']['encryptable-fields.cipher']);

        return 'base64:'.base64_encode($key);
    }

    /**
     * Set the application key in the environment file.
     *
     * @param  string  $key
     */
    protected function setKeyInEnvironmentFile($key): bool
    {
        $currentKey = $this->laravel['config'][$this->configKey()];

        if (
            ! empty($currentKey)
            && ! $this->option('force') && ! $this->confirm(sprintf(
                '%s is already set: values encrypted or hashed with it will become unusable. Overwrite it?',
                $this->envVariableName()
            ))
        ) {
            $this->warn(sprintf('%s not changed.', $this->envVariableName()));

            return false;
        }

        return $this->writeNewEnvironmentFileWith($key);
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     */
    protected function writeNewEnvironmentFileWith($key): bool
    {
        $environmentFilePath = $this->laravel->environmentFilePath();

        $replaced = preg_replace(
            $this->keyReplacementPattern(),
            $this->envVariableName().'='.$key,
            $input = file_get_contents($environmentFilePath),
        );

        // The pattern matches the loaded key: nothing is replaced when the .env value differs (e.g. cached config)
        if ($replaced === $input || $replaced === null) {
            $this->error(sprintf(
                'Unable to set %s: its .env value differs from the loaded configuration. Clear the config cache and retry.',
                $this->envVariableName(),
            ));

            return false;
        }

        file_put_contents($environmentFilePath, $replaced);

        return true;
    }

    /**
     * Get a regex pattern matching the environment variable with its currently loaded value.
     */
    protected function keyReplacementPattern(): string
    {
        return sprintf(
            '/^%s=%s/m',
            $this->envVariableName(),
            preg_quote($this->laravel['config'][$this->configKey()], '/'),
        );
    }
}
