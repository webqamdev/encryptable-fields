<?php

namespace Webqamdev\EncryptableFields\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Random\RandomException;
use stdClass;
use Webqamdev\EncryptableFields\EncryptableFieldsServiceProvider;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Providers\DatabaseEncryptionServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            EncryptableFieldsServiceProvider::class,
            DatabaseEncryptionServiceProvider::class,
        ];
    }

    /**
     * @throws RandomException
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set(
            'encryptable-fields.key',
            'base64:'.base64_encode(DatabaseEncrypter::generateKey($app['config']['encryptable-fields.cipher'])),
        );
        $app['config']->set('encryptable-fields.hash_key', 'base64:'.base64_encode(random_bytes(32)));

        if (getenv('DB_CONNECTION') === false) {
            $app['config']->set('database.default', 'testing');
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->text('firstname')->nullable();
            $table->char('firstname_hash', 64)->nullable();
            $table->text('email')->nullable();
            $table->text('nickname')->nullable();
        });
    }

    /**
     * Raw database row, bypassing Eloquent casts.
     */
    protected function row(int $id): stdClass
    {
        $row = DB::table('users')->where('id', $id)->first();
        $this->assertInstanceOf(stdClass::class, $row);

        return $row;
    }
}
