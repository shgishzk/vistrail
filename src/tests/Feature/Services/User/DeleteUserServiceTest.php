<?php

namespace Tests\Feature\Services\User;

use App\Models\User;
use App\Services\User\DeleteUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user deletion with the service.
     */
    public function test_it_deletes_a_user(): void
    {
        $user = User::factory()->create();
        
        $service = new DeleteUserService();
        
        $result = $service->execute($user);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
    
    /**
     * Test service returns false if deletion fails.
     */
    public function test_it_returns_false_if_deletion_fails(): void
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('delete')->willReturn(false);
        
        $service = new DeleteUserService();
        
        $result = $service->execute($mockUser);
        
        $this->assertFalse($result);
    }
}
