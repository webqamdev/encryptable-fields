# Eloquent model encrypted fields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thomascombe/encryptable-fields.svg?style=flat-square)](https://packagist.org/packages/thomascombe/encryptable-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/thomascombe/encryptable-fields.svg?style=flat-square)](https://packagist.org/packages/thomascombe/encryptable-fields)

Allow you to encrypt some model fields. You can add a hashed field to allow sql query
 
## Installation

You can install the package via composer:

```bash
composer require thomascombe/encryptable-fields
```

## Usage

``` php
<?php

namespace App\Models;

use Thomascombe\EncryptableFields\Models\Traits\EncryptableFields;

class User extends
{
    use EncryptableFields;

    const COLUMN_USER_LASTNAME = 'user_lastname';
    const COLUMN_USER_LASTNAME_HASH = 'user_lastname_hash';
    const COLUMN_USER_FIRSTNAME = 'user_firstname';
    const COLUMN_USER_FIRSTNAME_HASH = 'user_firstname_hash';
    const COLUMN_USER_MAIL = 'user_mail';

    protected $encryptable = [
        self::COLUMN_USER_FIRSTNAME => self::COLUMN_USER_FIRSTNAME_HASH,
        self::COLUMN_USER_LASTNAME => self::COLUMN_USER_LASTNAME_HASH,
        self::COLUMN_USER_MAIL,
    ];
```

### Create
```php
User::create(
    [
        User::COLUMN_USER_FIRSTNAME => 'watson',
        User::COLUMN_USER_LASTNAME => 'jack',
    ]
);
```

To find Model :
```php
User::where(User::COLUMN_USER_FIRSTNAME_HASH, self::hashValue('watson');
```

or 
``` php
User::whereEncrypted(User::COLUMN_USER_FIRSTNAME, 'watson')->get()
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Thomas Combe](https://github.com/thomascombe)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
