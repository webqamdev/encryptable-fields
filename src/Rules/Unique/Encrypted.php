<?php

namespace Webqamdev\EncryptableFields\Rules\Unique;

use Illuminate\Validation\Rules\Unique;
use Webqamdev\EncryptableFields\Rules\Traits\Encrypted as EncryptedTrait;

class Encrypted extends Unique
{
    use EncryptedTrait;
}
