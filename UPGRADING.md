# Upgrade guidelines

[[_TOC_]]

## 3.x to 4.x

### Scopes

#### `whereEncrypted` and `whereHashed` scopes
**Likelihood Of Impact: Low**

The scope `whereEncrypted` has been renamed to `whereHashed` to better reflect the underlying functionality.
The scope still works the same way, but the you must update your code to use the new scope.

A new scope `whereEncrypted` has been added to the trait. This scope now check the encrypted value of the field instead
of the hashed value. It also falls back to the hashed value if the encryption service is not
`Webqamdev\EncryptableFields\Services\DatabaseEncryption`.

#### Added `orWhere` and `whereNot` scopes
**Likelihood Of Impact: Low**

The `Webqamdev\EncryptableFields\Models\Traits\EncryptableFields` trait has received new `scopeOrWhereHashed`,
`scopeWhereNotHashed`, `scopeOrWhereEncrypted` and `scopeWhereNotEncrypted` methods.

If your application or package defines a class that implements this trait, you may want to update your code to use
the new scopes.

## 2.x to 3.x

### Updating Dependencies

#### Updated Laravel version requirement
**Likelihood Of Impact: High**

Minimum Laravel version has been updated to `^10.0`. Update your `composer.json` file to require `laravel/framework`
version `^10.0` or higher. Laravel's upgrade guide can be found [here](https://laravel.com/docs/10.x/upgrade).
