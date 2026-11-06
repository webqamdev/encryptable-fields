# Upgrade guide

## From 3.x to 4.0

v4 changes how values are hashed and, with `DatabaseEncryption`, how they are encrypted. **Existing data must be
migrated**: until then, hash lookups (`whereEncrypted`, `Hashed` rules, `eloquent-hashed` auth provider) find nothing
and, with `DatabaseEncryption`, values can't be decrypted.

v4 requires PHP 8.3+ and Laravel 13: upgrade them first.

### 1. Back up your database

The migration rewrites encrypted and hash columns in place.

### 2. Update the package

```bash
composer require webqamdev/encryptable-fields:^4.0
```

### 3. Update the configuration

If you published `config/encryptable-fields.php`, replace it with the new one:

```bash
php artisan vendor:publish --provider="Webqamdev\EncryptableFields\EncryptableFieldsServiceProvider" --force
```

then re-apply your customizations (e.g. `encryption`). The `hash_salt` option was removed. The `cipher`, `hash_key`
and `legacy_key` options were added.

### 4. Widen hash columns

Hashes are now 64 characters long (40 with v3). Create a migration for each hash column:

```php
Schema::table('users', function (Blueprint $table) {
    $table->char('firstname_hash', 64)->nullable()->change();
});
```

### 5. Generate the hash key

Add `APP_DB_HASH_KEY=` to your `.env` file, then:

```bash
php artisan encryptable-fields:key-generate --hash
```

Repeat in every environment: each one gets its own key. Keep it secret and backed up, like `APP_KEY`.

### 6. Database encryption only: generate a new key

Skip this step if you use the default `Encryption` service.

1. Copy your current `APP_DB_ENCRYPTION_KEY` value to a new `APP_DB_ENCRYPTION_LEGACY_KEY` variable.
2. Choose your cipher, according to your database server version:

   | `APP_DB_ENCRYPTION_CIPHER` | Requires                        |
   |----------------------------|---------------------------------|
   | `aes-128-ecb` (default)    | MySQL 8.0.21+ / MariaDB 10.2.3+ |
   | `aes-256-cbc`              | MySQL 8.0.21+ / MariaDB 11.2+   |

   `aes-128-ecb` gives identical encrypted values for identical values: prefer `aes-256-cbc` when available.
3. Generate a new key:

   ```bash
   php artisan encryptable-fields:key-generate --force
   ```

Your `.env` file now contains:

```
APP_DB_ENCRYPTION_CIPHER=aes-256-cbc
APP_DB_ENCRYPTION_KEY=base64:...new key...
APP_DB_ENCRYPTION_LEGACY_KEY=base64:...v3 key...
APP_DB_HASH_KEY=base64:...
```

### 7. Migrate data

Put the application in maintenance mode, then run the command with every model using the `EncryptableFields` trait:

```bash
php artisan down
php artisan encryptable-fields:migrate-legacy "App\Models\User" "App\Models\Customer"
php artisan up
```

In production, it asks for confirmation: add `--force` to skip it. The command:

- recomputes every hash with the new key,
- with `DatabaseEncryption`, decrypts values with the v3 key and encrypts them with the new one.

It bypasses model events and global scopes (soft-deleted rows are migrated too). If it stops, run it again: values
already migrated are skipped. Use `--chunk=` to change the number of rows loaded at once (500 by default).

Once done, remove `APP_DB_ENCRYPTION_LEGACY_KEY` from your `.env` file.

### 8. Check your code

| v3                                                                                         | v4                                                                         |
|--------------------------------------------------------------------------------------------|----------------------------------------------------------------------------|
| `Webqamdev\EncryptableFields\Models\Traits\EncryptableFields`, `HasEncryptableFieldsLog`  | `Webqamdev\EncryptableFields\Models\Concerns\...`                          |
| `Webqamdev\EncryptableFields\Http\Controllers\Admin\Traits\EncryptedSearchTrait`        | `Webqamdev\EncryptableFields\Http\Controllers\Admin\Concerns\EncryptedSearchTrait` |
| `Webqamdev\EncryptableFields\Rules\Traits\Encrypted`, `Hashed`                            | `Webqamdev\EncryptableFields\Rules\Concerns\...`                           |
| Custom `EncryptionInterface` implementation: `static encrypt($value)`, `static decrypt(string $value)` | Instance methods: `encrypt(mixed $value): string`, `decrypt(string $value): mixed` |
| `$model->scopeWhereEncrypted($query, ...)` called directly                                 | `Model::whereEncrypted(...)`: the scope is declared with the `#[Scope]` attribute |
| `dbHashValue($value, 'sha256')`                                                            | `dbHashValue($value)`: always HMAC-SHA256, the algorithm argument is ignored |
| `$model->setEncryptedAttribute()`, `setHashedAttribute()`, `getEncryptedAttribute()`, `getDecryptValue()` | Removed: assign the attribute, the cast handles encryption and hashing |
| `setAttribute()` / `getAttributeFromArray()` overridden by the trait                       | Not overridden anymore: encryption is an Eloquent cast                     |
| `new DatabaseEncrypter($key)` hashing `$key`                                               | `new DatabaseEncrypter($rawKey, $cipher)`: 16 raw bytes for `aes-128-ecb`, 32 for `aes-256-cbc` |
| `DatabaseEncrypter::CIPHER`, `KEY_HASH_ALGORITHM`, `KEY_LENGTH`                            | Removed: use `CIPHER_AES_128_ECB` / `CIPHER_AES_256_CBC`                   |
| Raw SQL with the key written in it                                                         | Use `Support\DB::getClause()`: the key now lives in the `@encryptable_fields_key` session variable |
| `Support\DB::getRawClause()` returning `Illuminate\Database\Query\Expression`              | Returns the `Illuminate\Contracts\Database\Query\Expression` interface |
| `new Hashed($table, $column, false)` still adding `_hash`                                  | `false` keeps the column name as is                                        |
| `new Encrypted($table)` / `new Hashed($table)` without column                              | `$column` is required                                                      |
| `'0'` stored as `null`                                                                     | `'0'` is encrypted and hashed                                              |

Casts declared on encrypted attributes in your models are overridden by the package cast: remove them.

To update the trait imports in your application:

```bash
grep -rl 'EncryptableFields\\.*\\Traits\\' app | xargs sed -i '' '/Webqamdev\\EncryptableFields/s/\\Traits\\/\\Concerns\\/'
```

(`sed -i` without `''` on Linux.)
