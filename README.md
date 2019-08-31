# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thomascombe/encryptable-fields.svg?style=flat-square)](https://packagist.org/packages/thomascombe/encryptable-fields)
[![Build Status](https://img.shields.io/travis/thomascombe/encryptable-fields/master.svg?style=flat-square)](https://travis-ci.org/thomascombe/encryptable-fields)
[![Quality Score](https://img.shields.io/scrutinizer/g/thomascombe/encryptable-fields.svg?style=flat-square)](https://scrutinizer-ci.com/g/thomascombe/encryptable-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/thomascombe/encryptable-fields.svg?style=flat-square)](https://packagist.org/packages/thomascombe/encryptable-fields)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

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

    protected $encryptable = [
        self::COLUMN_USER_FIRSTNAME => self::COLUMN_USER_FIRSTNAME_HASH,
        self::COLUMN_USER_LASTNAME => self::COLUMN_USER_LASTNAME_HASH,
    ];
```

To find user :
```php
User::where(User::COLUMN_USER_FIRSTNAME_HASH, self::hashValue('watson');
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email thomascombe42@gmail.com instead of using the issue tracker.

## Credits

- [Thomas Combe](https://github.com/thomascombe)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
