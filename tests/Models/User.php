<?php

namespace Webqamdev\EncryptableFields\Tests\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Webqamdev\EncryptableFields\Models\Concerns\EncryptableFields;

/**
 * @property int $id
 * @property ?string $firstname
 * @property ?string $email
 * @property ?string $nickname
 */
class User extends Model
{
    use EncryptableFields;

    public $timestamps = false;

    protected $guarded = [];

    /** @var array<int|string, string> */
    protected $encryptable = [
        'firstname' => 'firstname_hash',
        'email',
        'nickname',
    ];

    public function getEmailAttribute(?string $value): ?string
    {
        return $value === null ? null : strtoupper($value);
    }

    /**
     * @return Attribute<non-falsy-string|null, never>
     */
    protected function nickname(): Attribute
    {
        return Attribute::get(fn (?string $value): ?string => $value === null ? null : '@'.$value);
    }
}
