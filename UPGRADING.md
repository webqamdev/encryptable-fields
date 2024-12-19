# Upgrade guidelines

[[_TOC_]]

## 3.x to 4.x

### Scopes

#### `whereEncrypted` and `whereHashed` scopes
**Likelihood Of Impact: Low**

The scope `whereEncrypted` now check the encrypted value of the field instead of the hashed value. It also falls back
to it's previous behavior if the encryption service is not `Webqamdev\EncryptableFields\Services\DatabaseEncryption`.

A new scope `whereHashed` has been added to check the hashed value of the field. This scope works the same way as the
previous `whereEncrypted` scope.

#### Added `orWhere` and `whereNot` scopes
**Likelihood Of Impact: Low**

The `Webqamdev\EncryptableFields\Models\Traits\EncryptableFields` trait has received new `scopeOrWhereHashed`,
`scopeWhereHashedNot`, `scopeOrWhereEncrypted` and `scopeWhereEncryptedNot` methods.

If your application or package defines a class that implements this trait, you may want to update your code to use
the new scopes.

## 2.x to 3.x

### Updating Dependencies

#### Updated Laravel version requirement
**Likelihood Of Impact: High**

Minimum Laravel version has been updated to `^10.0`. Update your `composer.json` file to require `laravel/framework`
version `^10.0` or higher. Laravel's upgrade guide can be found [here](https://laravel.com/docs/10.x/upgrade).
