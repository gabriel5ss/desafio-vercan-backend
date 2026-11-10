<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\LoginRequest;
use Override;
use PHPUnit\Framework\TestCase;

final class LoginRequestTest extends TestCase
{
    protected LoginRequest $loginFormRequest;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->loginFormRequest = new LoginRequest;
    }

    /**
     * A basic unit test example.
     */
    public function test_rules(): void
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ];

        $this->assertEquals($rules, $this->loginFormRequest->rules());
    }
}
