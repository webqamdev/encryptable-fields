<?php

namespace Webqamdev\EncryptableFields\Rules\Exists;

use Illuminate\Validation\Rules\Exists;
use Webqamdev\EncryptableFields\Rules\Traits\Encrypted as EncryptedTrait;

class Encrypted extends Exists
{
    use EncryptedTrait;
}
