<?php

namespace Webqamdev\EncryptableFields\Tests;

use Illuminate\Support\Facades\DB;
use ReflectionException;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Exceptions\NotHashedFieldException;
use Webqamdev\EncryptableFields\Services\DatabaseEncryption;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;
use Webqamdev\EncryptableFields\Tests\Models\User;

class MigrateLegacyCommandTest extends TestCase
{
    private const string LEGACY_KEY = 'base64:bGVnYWN5LWtleS0xMjM0NQ==';

    private const string LEGACY_SALT = '--mDwt\k+PY,}vUJf2WeYUJ]yb(7A?>>bu7fGZrDpRUn#-kab';

    /**
     * @throws NotHashedFieldException
     */
    public function test_rehashes_values_encrypted_with_laravel_encrypter(): void
    {
        $id = DB::table('users')->insertGetId([
            'firstname' => encrypt('watson'),
            'firstname_hash' => sha1('watson'.self::LEGACY_SALT),
        ]);

        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class]])->assertSuccessful();

        $this->assertSame('watson', User::whereHashed('firstname', 'watson')->sole()->firstname);
        $this->assertSame($id, User::whereHashed('firstname', 'watson')->sole()->id);
    }

    /**
     * @throws NotHashedFieldException
     * @throws ReflectionException
     */
    public function test_reencrypts_legacy_database_values(): void
    {
        config(['encryptable-fields.legacy_key' => self::LEGACY_KEY]);
        $this->app->bind(EncryptionInterface::class, DatabaseEncryption::class);

        $legacy = new DatabaseEncrypter(substr(hash('sha512', self::LEGACY_KEY), 0, 16));
        DB::table('users')->insert([
            'firstname' => $legacy->encrypt('watson'),
            'firstname_hash' => sha1('watson'.self::LEGACY_SALT),
            'email' => $legacy->encrypt('0'),
        ]);

        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class]])
            ->expectsOutputToContain('1 row(s) migrated')
            ->assertSuccessful();

        $user = User::whereHashed('firstname', 'watson')->sole();
        $this->assertSame('watson', $user->firstname);
        $this->assertSame('0', $user->getAttributes()['email'] === null ? null : app(DatabaseEncrypter::class)->decrypt($user->getAttributes()['email']));

        // Second run skips already migrated values
        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class]])
            ->expectsOutputToContain('0 row(s) migrated')
            ->assertSuccessful();
    }

    /**
     * @throws NotHashedFieldException
     * @throws ReflectionException
     */
    public function test_encrypts_plain_values(): void
    {
        $this->app->bind(EncryptionInterface::class, DatabaseEncryption::class);

        DB::table('users')->insert(['firstname' => 'watson', 'email' => '0']);
        DB::table('users')->insert(['firstname' => dbEncrypt('holmes'), 'firstname_hash' => dbHashValue('holmes')]);

        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class], '--from-plain' => true])
            ->expectsOutputToContain('1 row(s) migrated')
            ->assertSuccessful();

        $user = User::whereEncrypted('firstname', 'watson')->sole();
        $this->assertSame('watson', $user->firstname);
        $this->assertSame('0', dbDecrypt($user->getAttributes()['email']));
        $this->assertSame('holmes', User::whereEncrypted('firstname', 'holmes')->sole()->firstname);

        // Second run skips already encrypted values
        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class], '--from-plain' => true])
            ->expectsOutputToContain('0 row(s) migrated')
            ->assertSuccessful();
    }

    public function test_validates_every_model_before_migrating(): void
    {
        DB::table('users')->insert(['firstname' => encrypt('watson'), 'firstname_hash' => 'legacy']);

        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class, 'App\\Missing']])->assertFailed();

        $this->assertSame('legacy', DB::table('users')->value('firstname_hash'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_fails_without_legacy_key_when_using_database_encryption(): void
    {
        $this->app->bind(EncryptionInterface::class, DatabaseEncryption::class);

        $this->artisan('encryptable-fields:migrate-legacy', ['models' => [User::class]])->assertFailed();
    }
}
