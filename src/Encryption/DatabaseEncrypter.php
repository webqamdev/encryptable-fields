<?php

namespace Webqamdev\EncryptableFields\Encryption;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Encryption\Encrypter as BaseEncrypter;
use JsonException;
use Random\RandomException;
use RuntimeException;
use SensitiveParameter;

/**
 * Encrypter whose payloads can also be decrypted by MySQL / MariaDB `aes_decrypt`.
 */
class DatabaseEncrypter extends BaseEncrypter
{
    public const string CIPHER_AES_128_ECB = 'aes-128-ecb';

    public const string CIPHER_AES_256_CBC = 'aes-256-cbc';

    /**
     * @param  string  $key  Raw key: 16 bytes for aes-128-ecb, 32 bytes for aes-256-cbc
     *
     * @throws RuntimeException
     */
    public function __construct(#[SensitiveParameter] string $key, string $cipher = self::CIPHER_AES_128_ECB)
    {
        parent::__construct($key, strtolower($cipher));
    }

    public static function supported(#[SensitiveParameter] $key, $cipher): bool
    {
        return match (strtolower($cipher)) {
            self::CIPHER_AES_128_ECB => strlen($key) === 16,
            self::CIPHER_AES_256_CBC => parent::supported($key, $cipher),
            default => false,
        };
    }

    /**
     * @throws RandomException
     */
    public static function generateKey($cipher): string
    {
        return random_bytes(strtolower($cipher) === self::CIPHER_AES_128_ECB ? 16 : 32);
    }

    public function isEcb(): bool
    {
        return $this->cipher === self::CIPHER_AES_128_ECB;
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     *
     * @throws EncryptException
     */
    public function encrypt(#[SensitiveParameter] $value, $serialize = false): string
    {
        if (! $this->isEcb()) {
            return parent::encrypt($value, $serialize);
        }

        // ECB is not handled by Laravel's encrypter: no IV, MAC kept to detect tampering.
        $iv = '';

        $value = openssl_encrypt($serialize ? serialize($value) : $value, $this->cipher, $this->key);

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        $mac = $this->hash($iv, $value, $this->key);

        try {
            return base64_encode(json_encode(['iv' => $iv, 'value' => $value, 'mac' => $mac], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            throw new EncryptException('Could not encrypt the data.', $e->getCode(), previous: $e);
        }
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool  $unserialize
     *
     * @throws DecryptException
     */
    public function decrypt($payload, $unserialize = false): mixed
    {
        if (! $this->isEcb()) {
            return parent::decrypt($payload, $unserialize);
        }

        $payload = json_decode(base64_decode($payload), true);

        if (! $this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }

        if (! $this->validMac($payload)) {
            throw new DecryptException('The MAC is invalid.');
        }

        $decrypted = openssl_decrypt($payload['value'], $this->cipher, $this->key);

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }
}
