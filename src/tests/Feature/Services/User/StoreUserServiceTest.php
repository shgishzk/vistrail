<?php

namespace Tests\Feature\Services\User;

use App\Models\User;
use App\Services\User\StoreUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreUserServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user creation with the service.
     */
    public function test_it_creates_a_user(): void
    {
        $service = new StoreUserService();
        
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ];
        
        $user = $service->execute($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
    
    /**
     * Test password is properly hashed.
     */
    public function test_it_hashes_the_password(): void
    {
        $service = new StoreUserService();
        
        $plainPassword = 'Password123';
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $plainPassword,
        ];
        
        $user = $service->execute($userData);
        
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }
    
    /**
     * Test user is created with correct attributes.
     */
    public function test_it_creates_user_with_correct_attributes(): void
    {
        $service = new StoreUserService();
        
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
        ];
        
        $user = $service->execute($userData);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
