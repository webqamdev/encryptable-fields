<?php

namespace Webqamdev\EncryptableFields\Console;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Console\KeyGenerateCommand as BaseCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

class KeyGenerateCommand extends BaseCommand
{
    protected const ENV_VARIABLE_NAME = 'APP_DB_ENCRYPTION_KEY';
    protected const CONFIG_KEY = 'encryptable-fields.key';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryptable-fields:key-generate
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->hasEnvironmentVariable()) {
            $this->error(
                sprintf("Missing environment variable. Please add %s= to your .env file.", self::ENV_VARIABLE_NAME)
            );
            return;
        }

        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            $this->line('<comment>' . $key . '</comment>');
            return;
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if (!$this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->laravel['config'][self::CONFIG_KEY] = $key;

        $this->info('Application key set successfully.');
    }

    protected function hasEnvironmentVariable(): bool
    {
        return Str::contains(
            File::get(
                $this->laravel->environmentFilePath()
            ),
            self::ENV_VARIABLE_NAME . '='
        );
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey(): string
    {
        return 'base64:' . base64_encode(Encrypter::generateKey(DatabaseEncrypter::CIPHER));
    }

    /**
     * Set the application key in the environment file.
     *
     * @param string $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key): bool
    {
        $currentKey = $this->laravel['config'][self::CONFIG_KEY];

        if (strlen($currentKey) !== 0 && (!$this->confirmToProceed())) {
            return false;
        }

        $this->writeNewEnvironmentFileWith($key);

        return true;
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param string $key
     * @return void
     */
    protected function writeNewEnvironmentFileWith($key): void
    {
        $environmentFilePath = $this->laravel->environmentFilePath();

        file_put_contents(
            $environmentFilePath,
            preg_replace(
                $this->keyReplacementPattern(),
                self::ENV_VARIABLE_NAME . '=' . $key,
                file_get_contents($environmentFilePath)
            )
        );
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern(): string
    {
        return sprintf(
            "/^%s=%s/m",
            self::ENV_VARIABLE_NAME,
            preg_quote($this->laravel['config'][self::CONFIG_KEY], '/')
        );
    }
}
