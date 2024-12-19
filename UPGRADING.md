# Upgrade guidelines

[[_TOC_]]

## 3.x to 4.x

### Scopes

#### Renamed `whereEncrypted` to `whereHashed`
**Likelihood Of Impact: High**

The scope `whereEncrypted` has been renamed to `whereHashed` to better reflect the underlying functionality.
The scope still works the same way, but the you must update your code to use the new scope.

#### Added `orWhere` and `whereNot` scopes
**Likelihood Of Impact: Low**

The `Webqamdev\EncryptableFields\Models\Traits\EncryptableFields` trait has received new `scopeOrWhereHashed`,
`scopeWhereNotHashed`, `scopeOrWhereEncrypted` and `scopeWhereNotEncrypted` methods.

If your application or package defines a class that implements this trait, you may want to update your code to use
the new scopes.
