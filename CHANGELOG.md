# Changelog

All notable changes to `encryptable-fields` will be documented in this file

## [4.0.0](https://github.com/webqamdev/encryptable-fields/compare/3.2.0...master) - Unreleased

Breaking changes, see [UPGRADE.md](UPGRADE.md).

- Require PHP 8.3+ and Laravel 13
- Hash values with HMAC-SHA256 and a dedicated `APP_DB_HASH_KEY` instead of a salted SHA-1 (`hash_salt` config removed)
- Use the full database encryption key (128 bits instead of 64 bits) instead of a truncated SHA-512 of it
- Add `aes-256-cbc` database cipher (`APP_DB_ENCRYPTION_CIPHER`), for MySQL 5.6.17+ / MariaDB 11.2+
- Keep the database encryption key out of SQL statements, query log and exceptions (MySQL session variable)
- Replace `EncryptableFields` trait attribute overrides with an Eloquent cast (`Casts\Encrypted`)
- Fix `'0'` being stored as `null`
- Fix `DatabaseEncrypter` not serializing falsy values
- Add `encryptable-fields:key-generate --hash` option
- Add `encryptable-fields:migrate-legacy` command to migrate v3 data
- Move traits from `Traits` to `Concerns` namespaces
- Ask for confirmation before `encryptable-fields:key-generate` overwrites an existing key
- Fix `encryptable-fields:key-generate` reporting success when the `.env` value differs from the loaded key
- Fix `Hashed` rules ignoring the `$addHashSuffixIfNeeded` argument
- Fix `Exists` `Encrypted` and `Hashed` rules on array values
- Fix `HasEncryptableFieldsLog` logging non-hashed encrypted attributes in plain text, and new values as old ones
- Add Laravel Pint, Rector and Larastan, modernize code

## [3.2.0](https://github.com/webqamdev/encryptable-fields/compare/3.1.1...3.2.0) - 2026-06-01

- Laravel 13 compatibility

## [3.1.1](https://github.com/webqamdev/encryptable-fields/compare/3.1.0...3.1.1) - 2026-04-29

- Fix UTF-8MB4 conversion in JSON value decryption for database queries

## [3.1.0](https://github.com/webqamdev/encryptable-fields/compare/3.0.0...3.1.0) - 2025-08-13
- Laravel 12 compatibility

## [3.0.0](https://github.com/webqamdev/encryptable-fields/compare/2.4.0...3.0.0) - 2024-08-14
- Laravel 11 hash compatibility (missing key parameter)

## [2.4.0](https://github.com/webqamdev/encryptable-fields/compare/2.3.3...2.4.0) - 2024-06-25

- Laravel 11 compatibility

## [2.3.3](https://github.com/webqamdev/encryptable-fields/compare/2.3.2...2.3.3) - 2023-05-26

- Fix type hinting 

## [2.3.2](https://github.com/webqamdev/encryptable-fields/compare/2.3.1...2.3.2) - 2023-05-26

- Fix value decrypt when null 

## [2.3.1](https://github.com/webqamdev/encryptable-fields/compare/2.3.0...2.3.1) - 2023-05-16

- Fix value decrypt when using new accessor syntax 

## [2.3.0](https://github.com/webqamdev/encryptable-fields/compare/2.2.3...2.3.0) - 2023-03-10

- Laravel 10 compatibility

## [2.2.3](https://github.com/webqamdev/encryptable-fields/compare/2.2.2...2.2.3) - 2023-01-27

- Add parentheses to ternary operation

## [2.2.2](https://github.com/webqamdev/encryptable-fields/compare/2.2.1...2.2.2) - 2023-01-26

- Fix PHP 8.1 deprecation

## [2.2.1](https://github.com/webqamdev/encryptable-fields/compare/2.2.0...2.2.1) - 2023-01-10

- Handle attribute set mutators

## [2.2.0](https://github.com/webqamdev/encryptable-fields/compare/2.1.3...2.2.0) - 2022-05-24

- Laravel 9 compatibility

## [2.1.3](https://github.com/webqamdev/encryptable-fields/compare/2.1.2...2.1.3) - 2022-03-29

- Implement decrypt method instead of extending due to incompatibility with last version of Laravel

## [2.1.2](https://github.com/webqamdev/encryptable-fields/compare/2.1.1...2.1.2) - 2021-06-24

- Allow to save null value on encrypted column

## [2.1.1](https://github.com/webqamdev/encryptable-fields/compare/2.1.0...2.1.1) - 2021-06-17

- Check APP_DB_ENCRYPTION_KEY env variable existence when generating key

## [2.1.0](https://github.com/webqamdev/encryptable-fields/compare/2.0.0...2.1.0) - 2021-04-07

- Add exists rules
- Add `eloquent-hashed` auth provider

## [2.0.0](https://github.com/webqamdev/encryptable-fields/compare/1.6.1...2.0.0) - 2021-04-07

- Fix presence verifier registration in service provider
- Disable serialization with DatabaseEncrypter (breaking change)
- Update readme

## [1.6.1](https://github.com/webqamdev/encryptable-fields/compare/1.6.0...1.6.1) - 2021-03-22

- Fix used class namespace

## [1.6.0](https://github.com/webqamdev/encryptable-fields/compare/1.5.0...1.6.0) - 2021-03-22

- PHP 8 compatibility

## [1.5.0](https://github.com/webqamdev/encryptable-fields/compare/1.4.0...1.5.0) - 2021-02-01

- Add custom uniqueness rules
- Activity logger compatibility
- Fix EncryptedSearchTrait
- Use distinct cipher key between Laravel and database encryption
- Update readme

## [1.4.0](https://github.com/webqamdev/encryptable-fields/compare/1.3.0...1.4.0) - 2020-12-28

- Update Laravel requirements (L7 and L8)

## [1.3.0](https://github.com/webqamdev/encryptable-fields/compare/1.2.0...1.3.0) - 2020-05-14

- Add DatabaseEncrypter service to allow querying over encrypted values
- Add EncryptedSearchTrait
- Update Laravel requirements
- Update readme

## [1.2.0](https://github.com/webqamdev/encryptable-fields/compare/1.1.2...1.2.0) - 2019-11-21

- Change Laravel Required version 5.8 to >= 5.8

## [1.1.2](https://github.com/webqamdev/encryptable-fields/compare/1.1.1...1.1.2) - 2019-11-01

- Change some method visibility

## [1.1.1](https://github.com/webqamdev/encryptable-fields/compare/1.1.0...1.1.1) - 2019-10-23

- Convert to Webqamdev package

## [1.1.0](https://github.com/webqamdev/encryptable-fields/compare/1.0.3...1.1.0) - 2019-09-17

- Override attributesToArray to decrypt in toArray

## [1.0.3](https://github.com/webqamdev/encryptable-fields/compare/1.0.2...1.0.3) - 2019-09-07

- Fix error with encrypted field without hash

## [1.0.2](https://github.com/webqamdev/encryptable-fields/compare/1.0.1...1.0.2) - 2019-09-02

- Fix error with encrypted field without hash
- Clean code

## [1.0.1](https://github.com/webqamdev/encryptable-fields/compare/1.0.0...1.0.1) - 2019-08-31

- Rename configuration salt
- Update readme

## [1.0.0](https://github.com/webqamdev/encryptable-fields/releases/tag/1.0.0) - 2019-08-31

- Initial release
