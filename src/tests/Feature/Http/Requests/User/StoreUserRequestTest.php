<?php

namespace Tests\Feature\Http\Requests\User;

use App\Http\Requests\Admin\StoreUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    /**
     * Test validation passes with valid data.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
    
    /**
     * Test validation fails when required fields are missing.
     */
    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when email is invalid.
     */
    public function test_validation_fails_when_email_is_invalid(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123',
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password is too short.
     */
    public function test_validation_fails_when_password_is_too_short(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Abc12', // 5 characters, minimum is 6
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password is too long.
     */
    public function test_validation_fails_when_password_is_too_long(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Abc123' . str_repeat('x', 15), // 21 characters, maximum is 20
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password doesn't contain uppercase letters.
     */
    public function test_validation_fails_when_password_has_no_uppercase(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123', // No uppercase
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password doesn't contain lowercase letters.
     */
    public function test_validation_fails_when_password_has_no_lowercase(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'PASSWORD123', // No lowercase
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when password doesn't contain numbers.
     */
    public function test_validation_fails_when_password_has_no_numbers(): void
    {
        $request = new StoreUserRequest();
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'PasswordABC', // No numbers
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }
}
