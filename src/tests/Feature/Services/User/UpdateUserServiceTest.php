<?php

namespace Tests\Feature\Services\User;

use App\Models\User;
use App\Services\User\UpdateUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateUserServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user update with the service.
     */
    public function test_it_updates_a_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        
        $service = new UpdateUserService();
        
        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];
        
        $updatedUser = $service->execute($user, $userData);
        
        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('updated@example.com', $updatedUser->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }
    
    /**
     * Test password is properly hashed when provided.
     */
    public function test_it_hashes_the_password_when_provided(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);
        
        $service = new UpdateUserService();
        
        $plainPassword = 'NewPassword123';
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $plainPassword,
        ];
        
        $updatedUser = $service->execute($user, $userData);
        
        $this->assertNotEquals($plainPassword, $updatedUser->password);
        $this->assertTrue(Hash::check($plainPassword, $updatedUser->password));
    }
    
    /**
     * Test password is not updated when not provided.
     */
    public function test_it_does_not_update_password_when_not_provided(): void
    {
        $originalPassword = 'OriginalPass123';
        $user = User::factory()->create([
            'password' => Hash::make($originalPassword),
        ]);
        
        $service = new UpdateUserService();
        
        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => '', // Empty password
        ];
        
        $updatedUser = $service->execute($user, $userData);
        
        $this->assertTrue(Hash::check($originalPassword, $updatedUser->password));
    }
    
    /**
     * Test user is updated with correct attributes.
     */
    public function test_it_updates_user_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        
        $service = new UpdateUserService();
        
        $userData = [
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
        ];
        
        $updatedUser = $service->execute($user, $userData);
        
        $this->assertEquals('John Updated', $updatedUser->name);
        $this->assertEquals('john.updated@example.com', $updatedUser->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
        ]);
    }
}
