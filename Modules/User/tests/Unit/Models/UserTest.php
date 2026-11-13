<?php

namespace Modules\User\Tests\Unit\Models;

use Modules\User\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_get_full_name_attribute(): void
    {
        $user = new User();

        $user->name = 'Test';
        $user->last_name = 'User';

        $this->assertSame('Test User', $user->fullName());
    }
}
