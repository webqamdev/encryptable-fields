<?php

namespace Webqamdev\EncryptableFields\Tests;

class KeyGenerateCommandTest extends TestCase
{
    public function test_generates_keys_sized_for_their_usage(): void
    {
        $directory = sys_get_temp_dir().'/'.uniqid('ef-', true);
        mkdir($directory);
        file_put_contents($directory.'/.env', "APP_DB_ENCRYPTION_KEY=\nAPP_DB_HASH_KEY=\n");
        $this->app->useEnvironmentPath($directory);
        config(['encryptable-fields.key' => '', 'encryptable-fields.hash_key' => '']);

        $this->artisan('encryptable-fields:key-generate')->assertSuccessful();
        $this->artisan('encryptable-fields:key-generate', ['--hash' => true])->assertSuccessful();

        preg_match('/^APP_DB_ENCRYPTION_KEY=base64:(.+)$/m', file_get_contents($directory.'/.env'), $key);
        preg_match('/^APP_DB_HASH_KEY=base64:(.+)$/m', file_get_contents($directory.'/.env'), $hashKey);
        $this->assertSame(config('encryptable-fields.cipher') === 'aes-128-ecb' ? 16 : 32, strlen(base64_decode($key[1])));
        $this->assertSame(32, strlen(base64_decode($hashKey[1])));

        unlink($directory.'/.env');
        rmdir($directory);
    }

    public function test_asks_confirmation_before_overwriting_a_key(): void
    {
        $directory = sys_get_temp_dir().'/'.uniqid('ef-', true);
        mkdir($directory);
        file_put_contents($directory.'/.env', "APP_DB_HASH_KEY=base64:current\n");
        $this->app->useEnvironmentPath($directory);
        config(['encryptable-fields.hash_key' => 'base64:current']);

        $this->artisan('encryptable-fields:key-generate', ['--hash' => true])
            ->expectsConfirmation('APP_DB_HASH_KEY is already set: values encrypted or hashed with it will become unusable. Overwrite it?')
            ->assertSuccessful();
        $this->assertSame("APP_DB_HASH_KEY=base64:current\n", file_get_contents($directory.'/.env'));

        $this->artisan('encryptable-fields:key-generate', ['--hash' => true, '--force' => true])->assertSuccessful();
        $this->assertStringNotContainsString('base64:current', file_get_contents($directory.'/.env'));

        unlink($directory.'/.env');
        rmdir($directory);
    }

    public function test_reports_when_the_env_value_differs_from_the_loaded_key(): void
    {
        $directory = sys_get_temp_dir().'/'.uniqid('ef-', true);
        mkdir($directory);
        file_put_contents($directory.'/.env', "APP_DB_HASH_KEY=base64:in-env-file\n");
        $this->app->useEnvironmentPath($directory);
        config(['encryptable-fields.hash_key' => 'base64:cached']);

        $this->artisan('encryptable-fields:key-generate', ['--hash' => true, '--force' => true])
            ->expectsOutputToContain('Unable to set APP_DB_HASH_KEY')
            ->doesntExpectOutputToContain('set successfully');
        $this->assertSame("APP_DB_HASH_KEY=base64:in-env-file\n", file_get_contents($directory.'/.env'));

        unlink($directory.'/.env');
        rmdir($directory);
    }
}
