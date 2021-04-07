<?php

namespace Webqamdev\EncryptableFields\Rules\Exists;

use Illuminate\Validation\Rules\Exists;
use Webqamdev\EncryptableFields\Rules\Traits\Hashed as HashedTrait;

class Hashed extends Exists
{
    use HashedTrait;
}
