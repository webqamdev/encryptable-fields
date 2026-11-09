<?php

namespace Webqamdev\EncryptableFields\Rules\Exists;

use Illuminate\Validation\Rules\Exists;
use Webqamdev\EncryptableFields\Rules\Concerns\Hashed as HashedTrait;

class Hashed extends Exists
{
    use HashedTrait;
}
