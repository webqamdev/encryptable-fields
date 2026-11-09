<?php

namespace Webqamdev\EncryptableFields\Rules\Unique;

use Illuminate\Validation\Rules\Unique;
use Webqamdev\EncryptableFields\Rules\Concerns\Hashed as HashedTrait;

class Hashed extends Unique
{
    use HashedTrait;
}
