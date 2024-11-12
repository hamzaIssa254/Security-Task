<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function testIndexReturnsPaginatedUsers()
    {

        User::factory()->count(10)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'pagination' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'total_pages',
                ],
            ]);

        $response->assertJsonCount(2, 'data');
    }
    public function testStoreCreatesUser()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $user->roles()->attach($roles);

        $data = [
            'name' => 'Updated Name',
            'email' => 'testuser@example.com',
            'password' => 'newpassword123',
            'roles' => $roles->pluck('id')->toArray(),
        ];

        $response = $this->postJson(route('users.store'), $data);

        $response->assertStatus(201)
            ->assertJson(['message' => 'User created successfully']);
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    public function testUpdateModifiesUser()
    {

        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $user->roles()->attach($roles);

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword123',
            'roles' => $roles->pluck('id')->toArray(),
        ];

        $response = $this->putJson(route('users.update', $user), $data);

        $response->assertStatus(200)
            ->assertJson(['message' => 'User updated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        foreach ($roles as $role) {
            $this->assertDatabaseHas('role_user', [
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }
    }


    public function testDestroyDeletesUser()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(route('users.destroy', $user));

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
