<?php

namespace Tests\Feature\Http\Requests\User;

use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test validation passes with valid data.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
    
    /**
     * Test validation passes with valid data without password.
     */
    public function test_validation_passes_with_valid_data_without_password(): void
    {
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
    
    /**
     * Test validation fails when required fields are missing.
     */
    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
        });
        
        $validator = Validator::make([], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when email is invalid.
     */
    public function test_validation_fails_when_email_is_invalid(): void
    {
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'invalid-email',
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password is too short.
     */
    public function test_validation_fails_when_password_is_too_short(): void
    {
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Abc12', // 5 characters, minimum is 6
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password doesn't contain uppercase letters.
     */
    public function test_validation_fails_when_password_has_no_uppercase(): void
    {
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () {
            return new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123', // No uppercase
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test email uniqueness validation excludes current user.
     */
    public function test_email_uniqueness_validation_excludes_current_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com'
        ]);
        
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($user) {
            $route = new \Illuminate\Routing\Route('PUT', 'admin/users/{user}', []);
            $route->setParameter('user', $user->id);
            return $route;
        });
        
        $validator = Validator::make([
            'name' => 'Updated Name',
            'email' => 'existing@example.com',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
}
