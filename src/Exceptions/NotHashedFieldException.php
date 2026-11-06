<?php

namespace Webqamdev\EncryptableFields\Exceptions;

use Exception;

class NotHashedFieldException extends Exception
{
    public function __toString(): string
    {
        return self::class.': '.$this->message."\n";
    }
}
