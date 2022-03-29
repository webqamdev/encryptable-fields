<?php

namespace Webqamdev\EncryptableFields\Encryption;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Encryption\Encrypter as BaseEncrypter;
use RuntimeException;

class DatabaseEncrypter extends BaseEncrypter
{
    public const KEY_HASH_ALGORITHM = 'sha512';
    public const KEY_LENGTH = 16;

    public const CIPHER_AES_128_ECB = 'aes-128-ecb';

    public const CIPHER = self::CIPHER_AES_128_ECB;

    /**
     * Create a new encrypter instance.
     *
     * @param string $key
     * @throws RuntimeException
     */
    public function __construct(string $key)
    {
        // hash the key so that it could be easily used in MySQL
        $key = hash(self::KEY_HASH_ALGORITHM, $key);
        // only the 16 first chars will be used by openssl
        $key = substr($key, 0, self::KEY_LENGTH);

        parent::__construct($key, self::CIPHER);
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param string $key
     * @param string $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        return in_array($cipher, openssl_get_cipher_methods());
    }

    /**
     * Create a new encryption key for the given cipher.
     *
     * @param string $cipher
     * @return string
     * @throws Exception
     */
    public static function generateKey($cipher)
    {
        return random_bytes(self::KEY_LENGTH);
    }

    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @param bool $serialize
     * @return string
     * @throws EncryptException
     */
    public function encrypt($value, $serialize = false)
    {
        $iv = '';

        // First we will encrypt the value using OpenSSL. After this is encrypted we
        // will proceed to calculating a MAC for the encrypted value so that this
        // value can be verified later as not having been changed by the users.
        $value = openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        // Once we get the encrypted value we'll go ahead and base64_encode the input
        // vector and create the MAC for the encrypted value so we can then verify
        // its authenticity. Then, we'll JSON the data into the "payload" array.
        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Decrypt the given value.
     *
     * @param string $payload
     * @param bool $unserialize
     * @return mixed
     * @throws DecryptException
     */
    public function decrypt($payload, $unserialize = false)
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $tag = empty($payload['tag']) ? null : base64_decode($payload['tag']);

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller. If we are
        // unable to decrypt this value we will throw out an exception message.
        $decrypted = openssl_decrypt(
            $payload['value'],
            $this->cipher,
            $this->key,
            0,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param string $payload
     * @return array
     *
     * @throws DecryptException
     */
    protected function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value. We'll also check the MAC for this encryption.
        if (!$this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }

        if (!$this->validMac($payload)) {
            throw new DecryptException('The MAC is invalid.');
        }

        return $payload;
    }
}
