<?php

namespace Webqamdev\EncryptableFields\Rules\Unique;

use Illuminate\Validation\Rules\Unique;
use Webqamdev\EncryptableFields\Rules\Concerns\Encrypted as EncryptedTrait;

class Encrypted extends Unique
{
    use EncryptedTrait;
}
