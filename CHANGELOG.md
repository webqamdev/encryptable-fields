# Changelog

All notable changes to `encryptable-fields` will be documented in this file

## 2.0.0 - 2021-04-07

- Fix presence verifier registration in service provider
- Disable serialization with DatabaseEncrypter (breaking change)
- Update readme

## 1.6.1 - 2021-03-22

- Fix used class namespace

## 1.6.0 - 2021-03-22

- PHP 8 compatibility

## 1.5.0 - 2021-02-01

- Add custom uniqueness rules
- Activity logger compatibility
- Fix EncryptedSearchTrait
- Use distinct cipher key between Laravel and database encryption
- Update readme

## 1.4.0 - 2020-12-28

- Update Laravel requirements (L7 and L8)

## 1.3.0 - 2020-05-14

- Add DatabaseEncrypter service to allow querying over encrypted values
- Add EncryptedSearchTrait
- Update Laravel requirements
- Update readme

## 1.2.0 - 2019-11-21

- Change Laravel Required version 5.8 to >= 5.8

## 1.1.2 - 2019-11-01

- Change some method visibility

## 1.1.1 - 2019-10-23

- Convert to Webqamdev package

## 1.1.0 - 2019-09-17

- Override attributesToArray to decrypt in toArray

## 1.0.3 - 2019-09-07

- Fix error with encrypted field without hash

## 1.0.2 - 2019-09-02

- Fix error with encrypted field without hash
- Clean code

## 1.0.1 - 2019-08-31

- Rename configuration salt
- Update readme

## 1.0.0 - 2019-08-31

- Initial release
