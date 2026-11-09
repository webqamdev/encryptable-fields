<?php

namespace Webqamdev\EncryptableFields\Rules\Exists;

use Illuminate\Validation\Rules\Exists;
use Webqamdev\EncryptableFields\Rules\Concerns\Encrypted as EncryptedTrait;

class Encrypted extends Exists
{
    use EncryptedTrait;
}
