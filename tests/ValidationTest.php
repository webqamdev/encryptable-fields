<?php

namespace Webqamdev\EncryptableFields\Tests;

use Illuminate\Support\Facades\Validator;
use Webqamdev\EncryptableFields\Rules\Exists\Hashed as ExistsHashed;
use Webqamdev\EncryptableFields\Rules\Unique\Encrypted as UniqueEncrypted;
use Webqamdev\EncryptableFields\Rules\Unique\Hashed as UniqueHashed;
use Webqamdev\EncryptableFields\Tests\Models\User;

class ValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        User::query()->create(['firstname' => 'watson']);
        User::query()->create(['firstname' => 'holmes']);
    }

    public function test_hashed_rules(): void
    {
        $this->assertTrue(Validator::make(['name' => 'holmes'], ['name' => new ExistsHashed('users', 'firstname')])->passes());
        $this->assertTrue(Validator::make(['name' => 'moriarty'], ['name' => new ExistsHashed('users', 'firstname')])->fails());
        $this->assertTrue(Validator::make(['name' => 'holmes'], ['name' => new UniqueHashed('users', 'firstname')])->fails());
        $this->assertTrue(Validator::make(['name' => 'holmes'], ['name' => new ExistsHashed(User::class, 'firstname')])->passes());
    }

    public function test_hash_suffix_is_added_only_when_needed(): void
    {
        $this->assertStringContainsString(',firstname_hash,', (string) new UniqueHashed('users', 'firstname'));
        $this->assertStringNotContainsString('firstname_hash_hash', (string) new UniqueHashed('users', 'firstname_hash'));
        $this->assertStringContainsString(',firstname,', (string) new UniqueHashed('users', 'firstname', false));
    }

    public function test_encrypted_rule_uses_its_connection_grammar(): void
    {
        config(['database.connections.prefixed' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => 'pre_']]);

        $this->assertStringContainsString('pre_users', (string) new UniqueEncrypted('prefixed.users', 'users.email'));
        $this->assertStringNotContainsString('pre_users', (string) new UniqueEncrypted('users', 'users.email'));
    }

    public function test_exists_hashed_rule_on_array(): void
    {
        $rule = new ExistsHashed('users', 'firstname');

        $this->assertTrue(Validator::make(['names' => ['holmes', 'watson']], ['names' => $rule])->passes());
        $this->assertTrue(Validator::make(['names' => ['holmes', 'moriarty']], ['names' => $rule])->fails());
    }
}
