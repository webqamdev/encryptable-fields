<?php

namespace Webqamdev\EncryptableFields\Rules\Unique;

use Illuminate\Validation\Rules\Unique;
use Webqamdev\EncryptableFields\Rules\Traits\Hashed as HashedTrait;

class Hashed extends Unique
{
    use HashedTrait;
}
