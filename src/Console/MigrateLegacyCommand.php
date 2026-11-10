<?php

namespace Webqamdev\EncryptableFields\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Webqamdev\EncryptableFields\Encryption\DatabaseEncrypter;
use Webqamdev\EncryptableFields\Models\Concerns\EncryptableFields;
use Webqamdev\EncryptableFields\Services\DatabaseEncryption;
use Webqamdev\EncryptableFields\Services\EncryptionInterface;

/**
 * Re-computes v3 hashes (salted SHA-1) as HMAC-SHA256 and, with DatabaseEncryption,
 * re-encrypts v3 database values with the new key. With --from-plain, encrypts and hashes
 * values stored in plain text instead. Safe to run several times.
 */
class MigrateLegacyCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'encryptable-fields:migrate-legacy
                    {models* : Model classes using the EncryptableFields trait}
                    {--chunk=500 : Number of rows loaded at once}
                    {--from-plain : Encrypt and hash values stored in plain text}
                    {--force : Force the operation to run when in production}';

    protected $description = 'Migrate encrypted and hashed values from encryptable-fields v3 to v4';

    public function handle(EncryptionInterface $encryption): int
    {
        // Validate every model before updating any row
        foreach ($this->argument('models') as $class) {
            if (! is_subclass_of($class, Model::class) || ! in_array(EncryptableFields::class, class_uses_recursive($class), true)) {
                $this->error(sprintf('%s is not a model using the EncryptableFields trait.', $class));

                return self::FAILURE;
            }
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $legacyEncrypter = null;

        if ($encryption instanceof DatabaseEncryption && ! $this->option('from-plain')) {
            $legacyKey = config('encryptable-fields.legacy_key');

            if (empty($legacyKey)) {
                $this->error('Missing APP_DB_ENCRYPTION_LEGACY_KEY: set it to your v3 APP_DB_ENCRYPTION_KEY value.');

                return self::FAILURE;
            }

            // v3 key derivation: 16 first hexadecimal chars of the SHA-512 of the configured key
            $legacyEncrypter = new DatabaseEncrypter(substr(hash('sha512', $legacyKey), 0, 16));
        }

        foreach ($this->argument('models') as $class) {
            $count = 0;

            $class::query()->withoutGlobalScopes()->chunkById(
                (int) $this->option('chunk'),
                function ($models) use ($encryption, $legacyEncrypter, &$count): void {
                    foreach ($models as $model) {
                        $updates = $this->migrateModel($model, $encryption, $legacyEncrypter);

                        if ($updates !== []) {
                            $model->newQueryWithoutScopes()->whereKey($model->getKey())->toBase()->update($updates);
                            $count++;
                        }
                    }
                },
            );

            $this->info(sprintf('%s: %d row(s) migrated.', $class, $count));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function migrateModel(
        Model $model,
        EncryptionInterface $encryption,
        ?DatabaseEncrypter $legacyEncrypter
    ): array {
        $updates = [];

        // @phpstan-ignore method.notFound (checked in handle())
        foreach ($model->getEncryptableArray() as $key => $hashKey) {
            $raw = $model->getRawOriginal($key);

            if ($raw === null || $raw === '') {
                continue;
            }

            if ($this->option('from-plain')) {
                try {
                    $encryption->decrypt($raw);

                    continue; // already encrypted
                } catch (DecryptException) {
                    $value = $raw;
                }

                $updates[$key] = $encryption->encrypt($value);
            } elseif (! $legacyEncrypter instanceof DatabaseEncrypter) {
                $value = $encryption->decrypt($raw);
            } else {
                try {
                    $value = $legacyEncrypter->decrypt($raw);
                } catch (DecryptException) {
                    continue; // already migrated
                }

                $updates[$key] = $encryption->encrypt($value);
            }

            if ($hashKey !== null) {
                $updates[$hashKey] = dbHashValue((string) $value);
            }
        }

        return $updates;
    }
}
