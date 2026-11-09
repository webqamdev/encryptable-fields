<?php

namespace Webqamdev\EncryptableFields\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Webqamdev\EncryptableFields\Auth\EloquentHashedUserProvider;
use Webqamdev\EncryptableFields\Tests\Models\User;

class AuthProviderTest extends TestCase
{
    private EloquentHashedUserProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.providers.hashed' => ['driver' => 'eloquent-hashed', 'model' => User::class]]);
        $provider = Auth::createUserProvider('hashed');
        $this->assertInstanceOf(EloquentHashedUserProvider::class, $provider);
        $this->provider = $provider;

        User::query()->create(['firstname' => 'watson']);
        User::query()->create(['firstname' => 'holmes']);
    }

    public function test_retrieves_by_hashed_credentials(): void
    {
        $this->assertSame('holmes', $this->firstname(['firstname' => 'holmes', 'password' => 'secret']));
        $this->assertNull($this->provider->retrieveByCredentials(['firstname' => 'moriarty']));
        $this->assertNull($this->provider->retrieveByCredentials(['password' => 'secret']));
    }

    public function test_retrieves_by_array_and_closure_credentials(): void
    {
        $this->assertSame('holmes', $this->firstname(['firstname' => ['holmes', 'moriarty']]));
        $this->assertSame('holmes', $this->firstname(['firstname' => new Collection(['holmes'])]));
        $this->assertSame('watson', $this->firstname([
            'firstname' => ['holmes', 'watson'],
            'id' => fn (Builder $query) => $query->orderBy('id'),
        ]));
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function firstname(array $credentials): ?string
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        $this->assertInstanceOf(User::class, $user);

        return $user->firstname;
    }
}
