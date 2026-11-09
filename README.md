# Encrypted Eloquent fields for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/webqamdev/encryptable-fields.svg?style=flat-square)](https://packagist.org/packages/webqamdev/encryptable-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/webqamdev/encryptable-fields.svg?style=flat-square)](https://packagist.org/packages/webqamdev/encryptable-fields)

Encrypt Eloquent model attributes at rest, and optionally store a hash of each value so you can still query it.

> [!TIP]
> Upgrading from v3? Read the [upgrade guide](UPGRADE.md).

## Requirements

- PHP 8.3+
- Laravel 13
- For [searchable encrypted values](#searchable-encrypted-values): MySQL 8.0.21+ or MariaDB 10.2.3+ (see
  [cipher choice](#cipher-choice))
- Optional: [Filament](#filament) 5, [Backpack](#backpack)

## Installation

Install the package via Composer:

```bash
composer require webqamdev/encryptable-fields
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Webqamdev\EncryptableFields\EncryptableFieldsServiceProvider"
```

Hashed attributes are HMAC-SHA256 hashes. Add the following variable to your `.env` file:

```dotenv
APP_DB_HASH_KEY=
```

then generate the hash key:

```bash
php artisan encryptable-fields:key-generate --hash
```

> [!WARNING]
> Changing this key invalidates every stored hash. Keep it secret and backed up, like `APP_KEY`.

When a key is already set, `encryptable-fields:key-generate` asks for confirmation before overwriting it (the answer
defaults to no in non-interactive mode). Use `--force` to skip the confirmation, or `--show` to display a key without
writing it.

## Usage

Use the `EncryptableFields` trait in your model and list encrypted attributes in the `$encryptable` property. The trait
registers an Eloquent [cast](./src/Casts/Encrypted.php) for each of them.

To also store a hash of the plain value (useful for fast exact matches), use an associative entry: the encrypted
attribute as key, its hash attribute as value.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webqamdev\EncryptableFields\Models\Concerns\EncryptableFields;

class User extends Model
{
    use EncryptableFields;

    /**
     * The attributes that should be encrypted in database.
     *
     * @var array<int|string, string>
     */
    protected array $encryptable = [
        'name',
        'email' => 'email_hash',
    ];
}
```

Hash columns store a 64-character hexadecimal string:

```php
$table->text('email')->nullable();
$table->char('email_hash', 64)->nullable()->index();
```

Create models as usual:

```php
User::query()->create([
    'name' => 'John WATSON',
    'email' => 'john.watson@example.com',
]);
```

- `null` and empty strings are stored as `null`, in both encrypted and hash columns. Any other value, including `'0'`,
  is encrypted and hashed.
- Accessors defined on encrypted attributes (`firstname(): Attribute` or `getFirstnameAttribute()`) receive the
  decrypted value.

Find a model from a hashed value:

```php
User::query()->where('email_hash', dbHashValue('john.watson@example.com'))->first();
```

or with the model's local scopes:

```php
// Exact match on the hash column, throws NotHashedFieldException if the attribute has no hash column
User::query()->whereHashed('name', 'John WATSON')->first();

// Exact match on the decrypted value with DatabaseEncryption, falls back to whereHashed() otherwise
User::query()->whereEncrypted('name', 'John WATSON')->first();
```

With `DatabaseEncryption`, `whereEncrypted()` decrypts every row: prefer `whereHashed()` on large tables, hash columns
can be indexed.

### Authentication

The package registers an `eloquent-hashed` auth provider, which authenticates users on a hashed attribute such as an
email. Use it in `config/auth.php`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent-hashed',
        'model' => App\Models\User::class,
    ],
],
```

### Searchable encrypted values

[MySQL](https://dev.mysql.com/doc/refman/8.4/en/encryption-functions.html#function_aes-decrypt)
and [MariaDB](https://mariadb.com/kb/en/aes_decrypt/) both provide an `aes_decrypt()` function, which decrypts values
directly in SQL. It then becomes possible to filter or sort on encrypted values.

Values are then encrypted with a dedicated key and a cipher supported by your database server.

#### Cipher choice

| `APP_DB_ENCRYPTION_CIPHER` | Key size | Requires                        | Identical values                   |
|----------------------------|----------|---------------------------------|------------------------------------|
| `aes-128-ecb` (default)    | 128 bits | MySQL 8.0.21+ / MariaDB 10.2.3+ | ⚠️ give identical encrypted values |
| `aes-256-cbc`              | 256 bits | MySQL 8.0.21+ / MariaDB 11.2+   | give different encrypted values    |

> [!WARNING]
> With `aes-128-ecb`, anyone reading the database can tell which rows share the same value (and how many), without
> decrypting them. Use `aes-256-cbc` as soon as your database server supports it. Changing the cipher requires a new
> key and re-encrypting existing values.

#### Setup

1. Add the following variables to your `.env` file:

   ```dotenv
   APP_DB_ENCRYPTION_CIPHER=aes-128-ecb
   APP_DB_ENCRYPTION_KEY=
   ```

2. Generate a database encryption key matching the cipher:

   ```bash
   php artisan encryptable-fields:key-generate
   ```

   Don't generate this key on your own: its size depends on the cipher.

3. Register [DatabaseEncryptionServiceProvider](./src/Providers/DatabaseEncryptionServiceProvider.php) in
   `bootstrap/providers.php`. It registers a [DatabaseEncrypter](./src/Encryption/DatabaseEncrypter.php) singleton,
   which replaces Laravel's default encrypter for encrypted attributes:

   ```php
   return [
       App\Providers\AppServiceProvider::class,
       Webqamdev\EncryptableFields\Providers\DatabaseEncryptionServiceProvider::class,
   ];
   ```

4. Set the encryption service in `config/encryptable-fields.php`:

   ```php
   // Must implement EncryptionInterface
   'encryption' => Webqamdev\EncryptableFields\Services\DatabaseEncryption::class,
   ```

The key is never written in SQL statements: before the first query using it, the package stores it in a MySQL session
variable (`@encryptable_fields_key`) with a prepared statement, outside of Laravel's query log, events and exceptions.

> [!NOTE]
> The database [general query log](https://dev.mysql.com/doc/refman/8.4/en/query-log.html), when enabled, still
> records it.

> [!IMPORTANT]
> `DatabaseEncrypter` doesn't serialize values, so that exact values can be queried (`=` instead of `LIKE`). It can't
> handle objects or arrays.

#### Querying decrypted values

Use the clauses returned by `Webqamdev\EncryptableFields\Support\DB`:

```php
use Webqamdev\EncryptableFields\Support\DB;

User::query()->whereRaw(DB::getClause('firstname').' = ?', ['watson'])->get();
User::query()->orderBy(DB::getRawClause('firstname'))->get();
```

> [!CAUTION]
> A plain `LIKE` on an encrypted column silently returns nothing: the database only holds ciphertext. Hash columns
> (e.g. `firstname_hash`) only allow exact matches.

#### `searchEncrypted` scope

Any model using `EncryptableFields` gets a `searchEncrypted()` scope: a `LIKE` on the decrypted value of one or
several columns (`OR`).

```php
User::query()->searchEncrypted('wat', 'firstname')->get();
User::query()->searchEncrypted('wat', ['firstname', 'lastname'])->get();
```

- LIKE wildcards (`%`, `_`, `\`) typed by the user are escaped.
- Conditions are grouped, so they never leak into other `OR` conditions of the query.
- Columns not listed in `$encryptable` are matched as is, so encrypted and plain columns can be mixed.
- Case sensitivity follows the database collation.

### Filament

Once [searchable encrypted values](#searchable-encrypted-values) are set up, Filament's table search, filters and
global search must decrypt columns in SQL: by default, they run a `LIKE` on the raw column.

#### Table search and sort

Override the query of `searchable()` and `sortable()`:

```php
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Webqamdev\EncryptableFields\Support\DB;

TextColumn::make('firstname')
    ->searchable(query: fn (Builder $query, string $search): Builder => $query->searchEncrypted($search, 'firstname'))
    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy(DB::getRawClause('firstname'), $direction)),
```

Filament wraps each search closure in its own `where`/`orWhere` group, so the closure only adds its condition.

#### Custom filter

Search several encrypted columns from one input:

```php
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;

Filter::make('search')
    ->schema([
        TextInput::make('search')->label('Search (first or last name)'),
    ])
    ->query(fn (Builder $query, array $data): Builder => filled($data['search'] ?? null)
        ? $query->searchEncrypted($data['search'], ['firstname', 'lastname'])
        : $query)
    ->indicateUsing(fn (array $data): ?string => filled($data['search'] ?? null)
        ? 'Search: '.$data['search']
        : null),
```

#### Global search

Add the [SearchesEncryptedAttributes](./src/Filament/Concerns/SearchesEncryptedAttributes.php) trait to the resource
and declare the searched attributes:

```php
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Webqamdev\EncryptableFields\Filament\Concerns\SearchesEncryptedAttributes;

class UserResource extends Resource
{
    use SearchesEncryptedAttributes;

    // Result title, decrypted by the model cast
    protected static ?string $recordTitleAttribute = 'firstname';

    /**
     * @return string[]
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['firstname', 'lastname'];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return ['Last name' => $record->lastname];
    }
}
```

Without `getGloballySearchableAttributes()`, only the record title attribute is searched.

The trait replaces Filament's `applyGlobalSearchAttributeConstraints()` with `searchEncrypted()`.
`modifyGlobalSearchQuery()` can't be used instead: it runs after Filament's `LIKE` on raw columns, with an `AND`,
which would still exclude every row.

Differences with Filament's default global search:

- Attributes are flat: relationship attributes (`relation.column`) aren't supported.
- The search term is **not split into words**: `jack watson` is matched as a whole.
- Case sensitivity follows the database collation, `isGlobalSearchForcedCaseInsensitive()` is ignored.

### Backpack

For [Laravel Backpack](https://backpackforlaravel.com), the
[EncryptedSearchTrait](./src/Http/Controllers/Admin/Concerns/EncryptedSearchTrait.php) trait provides search and order
logics. `encryptedSearchLogic()` relies on the [`searchEncrypted` scope](#searchencrypted-scope), so the model must use
`EncryptableFields`.

```php
use Illuminate\Database\Eloquent\Builder;

CRUD::addColumn([
    // ...
    'searchLogic' => function (Builder $query, array $column, string $searchTerm): void {
        $this->encryptedSearchLogic($query, $column['name'], $searchTerm);
    },
    'orderLogic' => function (Builder $query, array $column, string $columnDirection): void {
        $this->encryptedOrderLogic($query, $column['name'], $columnDirection);
    },
]);
```

### Validation

Rules validate existence and uniqueness of a hashed or encrypted attribute. They extend
`Illuminate\Validation\Rules\Exists` and `Illuminate\Validation\Rules\Unique`.

| Rule                                                 | Checks                                     |
|------------------------------------------------------|--------------------------------------------|
| `Webqamdev\EncryptableFields\Rules\Exists\Hashed`    | a row exists with the same hash            |
| `Webqamdev\EncryptableFields\Rules\Unique\Hashed`    | no row has the same hash                   |
| `Webqamdev\EncryptableFields\Rules\Exists\Encrypted` | a row exists with the same decrypted value |
| `Webqamdev\EncryptableFields\Rules\Unique\Encrypted` | no row has the same decrypted value        |

Each rule takes a model class or a table name, then the attribute:

```php
use Webqamdev\EncryptableFields\Rules\Unique\Hashed;

public function rules(): array
{
    return [
        'email' => [
            new Hashed(User::class, 'email'),
            // or new Hashed('users', 'email'),
        ],
    ];
}
```

### Activity log

With [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog)
or [webqamdev/activity-logger-for-laravel](https://github.com/webqamdev/activity-logger-for-laravel), add the
`HasEncryptableFieldsLog` trait to each logged model: logs then contain encrypted values instead of decrypted ones.

## Testing

```bash
composer test
```

Code is modernized with [Rector](https://getrector.com), formatted with [Laravel Pint](https://laravel.com/docs/pint)
and analysed with [Larastan](https://github.com/larastan/larastan):

```bash
composer refactor
composer format
composer analyse
```

Searchable encrypted values tests only run against MySQL or MariaDB:

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=testing DB_USERNAME=root DB_PASSWORD=secret \
APP_DB_ENCRYPTION_CIPHER=aes-256-cbc composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Thomas Combe](https://github.com/thomascombe)

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
