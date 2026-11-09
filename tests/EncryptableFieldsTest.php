<?php

namespace Webqamdev\EncryptableFields\Tests;

use RuntimeException;
use Webqamdev\EncryptableFields\Tests\Models\User;

class EncryptableFieldsTest extends TestCase
{
    public function test_values_are_encrypted_hashed_and_decrypted(): void
    {
        $user = User::query()->create(['firstname' => 'watson', 'email' => 'jack@example.com', 'nickname' => 'jw']);

        $row = $this->row($user->id);
        $this->assertNotSame('watson', $row->firstname);
        $this->assertSame(
            hash_hmac('sha256', 'watson', base64_decode(substr(config('encryptable-fields.hash_key'), 7))),
            $row->firstname_hash,
        );

        $user = User::query()->find($user->id);
        $this->assertSame('watson', $user->firstname);
        $this->assertSame('JACK@EXAMPLE.COM', $user->email);
        $this->assertSame('@jw', $user->nickname);
        $this->assertSame(
            ['id' => $user->id, 'firstname' => 'watson', 'firstname_hash' => $row->firstname_hash, 'email' => 'JACK@EXAMPLE.COM', 'nickname' => '@jw'],
            $user->toArray(),
        );
    }

    public function test_zero_is_not_stored_as_null(): void
    {
        $user = User::query()->create(['firstname' => '0']);

        $this->assertNotNull($this->row($user->id)->firstname_hash);
        $this->assertSame('0', User::query()->find($user->id)->firstname);
        $this->assertTrue(User::whereEncrypted('firstname', '0')->exists());
    }

    public function test_empty_values_are_stored_as_null(): void
    {
        $user = User::query()->create(['firstname' => 'watson']);
        $user->update(['firstname' => '']);

        $row = $this->row($user->id);
        $this->assertNull($row->firstname);
        $this->assertNull($row->firstname_hash);
        $this->assertNull($user->fresh()->firstname);
    }

    public function test_where_encrypted_scope(): void
    {
        User::query()->create(['firstname' => 'watson']);
        User::query()->create(['firstname' => 'holmes']);

        $this->assertSame('holmes', User::whereEncrypted('firstname', 'holmes')->sole()->firstname);
    }

    public function test_reading_does_not_dirty_the_model(): void
    {
        $user = User::query()->create(['firstname' => 'watson'])->fresh();
        $user->getAttribute('firstname');

        $this->assertFalse($user->isDirty());
    }

    public function test_missing_hash_key_throws(): void
    {
        config(['encryptable-fields.hash_key' => null]);

        $this->expectException(RuntimeException::class);

        dbHashValue('watson');
    }
}
