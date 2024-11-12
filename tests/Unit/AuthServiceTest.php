<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $credentials = ['email' => $user->email, 'password' => 'password'];

        // توقع استدعاء Auth::attempt
        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn('fake_jwt_token');

        // توقع استدعاء Auth::user
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $response = $this->authService->login($credentials);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($user->email, $response['user']->email);
        $this->assertEquals('fake_jwt_token', $response['token']);
        $this->assertEquals(200, $response['code']);
    }

    /** @test */
    public function it_fails_to_login_with_invalid_credentials()
    {
        $credentials = ['email' => 'fake@example.com', 'password' => 'wrongpassword'];

        Auth::shouldReceive('attempt')->once()->with($credentials)->andReturn(false);

        $response = $this->authService->login($credentials);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Unauthorized', $response['message']);
        $this->assertEquals(401, $response['code']);
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        Auth::shouldReceive('login')->once()->andReturn('fake_jwt_token');

        $response = $this->authService->register($data);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('User created successfully', $response['message']);
        $this->assertEquals('fake_jwt_token', $response['token']);
        $this->assertEquals(201, $response['code']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function it_can_logout_the_user()
    {
        Auth::shouldReceive('logout')->once();

        $response = $this->authService->logout();

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Successfully logged out', $response['message']);
        $this->assertEquals(200, $response['code']);
    }
}
