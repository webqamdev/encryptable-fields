<?php

namespace Webqamdev\EncryptableFields\Tests;

use PHPUnit\Framework\TestCase;
use Random\RandomException;
use RuntimeException;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;

class DatabaseEncrypterTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_ecb_round_trip_is_deterministic(): void
    {
        $encrypter = new DatabaseEncrypter(DatabaseEncrypter::generateKey('aes-128-ecb'));

        $this->assertSame('watson', $encrypter->decrypt($encrypter->encrypt('watson')));
        $this->assertSame($encrypter->encrypt('watson'), $encrypter->encrypt('watson'));
        $this->assertSame('0', $encrypter->decrypt($encrypter->encrypt('0', true), true));
    }

    /**
     * @throws RandomException
     */
    public function test_cbc_round_trip_uses_random_iv(): void
    {
        $encrypter = new DatabaseEncrypter(DatabaseEncrypter::generateKey('aes-256-cbc'), 'aes-256-cbc');

        $this->assertSame('watson', $encrypter->decrypt($encrypter->encrypt('watson')));
        $this->assertNotSame($encrypter->encrypt('watson'), $encrypter->encrypt('watson'));
    }

    /**
     * @throws RandomException
     */
    public function test_generated_keys_use_full_cipher_size(): void
    {
        $this->assertSame(16, strlen(DatabaseEncrypter::generateKey('aes-128-ecb')));
        $this->assertSame(32, strlen(DatabaseEncrypter::generateKey('aes-256-cbc')));
    }

    /**
     * @throws RandomException
     */
    public function test_wrong_key_length_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);

        new DatabaseEncrypter(random_bytes(32));
    }
}
