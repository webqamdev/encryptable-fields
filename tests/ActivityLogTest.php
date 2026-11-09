<?php

namespace Webqamdev\EncryptableFields\Tests;

use Webqamdev\EncryptableFields\Casts\Encrypted;
use Webqamdev\EncryptableFields\Models\Concerns\HasEncryptableFieldsLog;
use Webqamdev\EncryptableFields\Tests\Models\User;

class ActivityLogTest extends TestCase
{
    public function test_logged_encrypted_values_are_stored_encrypted(): void
    {
        $model = new class extends User
        {
            use HasEncryptableFieldsLog {
                activityEncryptArray as public;
            }

            protected $table = 'users';
        };

        $user = $model->newQuery()->create(['firstname' => 'watson', 'email' => 'old@example.com']);
        // Same state as during the "updated" event: new attributes, original not synced yet
        $user->fill(['firstname' => 'holmes', 'email' => 'new@example.com']);

        $properties = $user->activityEncryptArray([
            'attributes' => ['firstname' => 'holmes', 'email' => 'NEW@EXAMPLE.COM'],
            'old' => ['firstname' => 'watson', 'email' => 'OLD@EXAMPLE.COM'],
        ]);

        $this->assertSame('holmes', Encrypted::decrypt($properties['attributes']['firstname']));
        $this->assertSame('new@example.com', Encrypted::decrypt($properties['attributes']['email']));
        $this->assertSame('watson', Encrypted::decrypt($properties['old']['firstname']));
        $this->assertSame('old@example.com', Encrypted::decrypt($properties['old']['email']));
    }
}
