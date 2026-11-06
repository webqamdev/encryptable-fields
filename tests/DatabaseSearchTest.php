<?php

namespace Webqamdev\EncryptableFields\Tests;

use Illuminate\Support\Facades\DB as Facade;
use Illuminate\Support\Facades\Validator;
use ReflectionException;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Http\Controllers\Admin\Concerns\EncryptedSearchTrait;
use Webqamdev\EncryptableFields\Rules\Exists\Encrypted as ExistsEncrypted;
use Webqamdev\EncryptableFields\Rules\Unique\Encrypted as UniqueEncrypted;
use Webqamdev\EncryptableFields\Services\DatabaseEncryption;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;
use Webqamdev\EncryptableFields\Support\DB;
use Webqamdev\EncryptableFields\Tests\Models\User;

/**
 * Runs against MySQL / MariaDB only, e.g. DB_CONNECTION=mysql DB_HOST=... vendor/bin/phpunit
 */
class DatabaseSearchTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(Facade::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Requires a MySQL or MariaDB connection.');
        }

        if (! app(DatabaseEncrypter::class)->isEcb() && $this->isMariaDbBelow112()) {
            $this->markTestSkipped('aes-256-cbc requires MariaDB 11.2+.');
        }

        $this->app->bind(EncryptionInterface::class, DatabaseEncryption::class);

        foreach (['Watson', 'Holmes', 'Ünïcode'] as $firstname) {
            User::query()->create(['firstname' => $firstname]);
        }
    }

    public function test_where_on_decrypted_value(): void
    {
        $this->assertSame(1, User::query()->whereRaw(DB::getClause('firstname').' = ?', ['Holmes'])->count());
        $this->assertSame(1, User::query()->whereRaw(DB::getClause('users.firstname').' = ?', ['Ünïcode'])->count());
    }

    public function test_search_and_order_logic(): void
    {
        $controller = new class
        {
            use EncryptedSearchTrait {
                encryptedSearchLogic as public;
                encryptedOrderLogic as public;
            }
        };

        $this->assertSame(['Holmes'], $controller->encryptedSearchLogic(User::query(), 'firstname', 'olm')->get()->pluck('firstname')->all());
        $this->assertSame(
            ['Holmes', 'Ünïcode', 'Watson'],
            $controller->encryptedOrderLogic(User::query(), 'firstname', 'asc')->get()->pluck('firstname')->all(),
        );
    }

    public function test_validation_rules(): void
    {
        $this->assertTrue(Validator::make(['name' => 'Holmes'], ['name' => new UniqueEncrypted('users', 'firstname')])->fails());
        $this->assertTrue(Validator::make(['name' => 'Moriarty'], ['name' => new UniqueEncrypted('users', 'firstname')])->passes());
        $this->assertTrue(Validator::make(['name' => 'Holmes'], ['name' => new ExistsEncrypted('users', 'firstname')])->passes());
        $this->assertTrue(Validator::make(['names' => ['Holmes', 'Watson']], ['names' => new ExistsEncrypted('users', 'firstname')])->passes());
        $this->assertTrue(Validator::make(['names' => ['Holmes', 'Moriarty']], ['names' => new ExistsEncrypted('users', 'firstname')])->fails());
    }

    public function test_key_never_reaches_query_log(): void
    {
        Facade::enableQueryLog();

        User::query()->whereRaw(DB::getClause('firstname').' = ?', ['Holmes'])->first();

        $key = base64_encode(app(DatabaseEncrypter::class)->getKey());
        foreach (Facade::getQueryLog() as $query) {
            $this->assertStringNotContainsString($key, $query['query'].json_encode($query['bindings']));
        }
        $this->assertNotEmpty(Facade::getQueryLog());
    }

    private function isMariaDbBelow112(): bool
    {
        $version = Facade::selectOne('select version() as v')->v;

        return str_contains(strtolower($version), 'mariadb') && version_compare($version, '11.2', '<');
    }
}
