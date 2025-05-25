<?php

namespace Tests\Feature\Http\Requests\Area;

use App\Http\Requests\Admin\UpdateAreaRequest;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateAreaRequestTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test validation passes with valid data.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $area = Area::factory()->create([
            'number' => 'A123',
        ]);
        
        $request = new UpdateAreaRequest();
        $request->setRouteResolver(function () use ($area) {
            $route = new \Illuminate\Routing\Route('PUT', 'admin/areas/{area}', []);
            $route->parameters = ['area' => $area->id];
            return $route;
        });
        
        $validator = Validator::make([
            'number' => 'B456',
            'name' => 'Updated Area',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
            'memo' => 'Updated memo',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
    
    /**
     * Test validation passes when using the same number for the same area.
     */
    public function test_validation_passes_when_using_same_number_for_same_area(): void
    {
        $area = Area::factory()->create([
            'number' => 'A123',
        ]);
        
        $request = new UpdateAreaRequest();
        $request->setRouteResolver(function () use ($area) {
            $route = new \Illuminate\Routing\Route('PUT', 'admin/areas/{area}', []);
            $route->parameters = ['area' => $area->id];
            return $route;
        });
        
        $validator = Validator::make([
            'number' => 'A123', // Same number
            'name' => 'Updated Area',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
    
    /**
     * Test validation fails when required fields are missing.
     */
    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $area = Area::factory()->create();
        
        $request = new UpdateAreaRequest();
        $request->setRouteResolver(function () use ($area) {
            $route = new \Illuminate\Routing\Route('PUT', 'admin/areas/{area}', []);
            $route->parameters = ['area' => $area->id];
            return $route;
        });
        
        $validator = Validator::make([
            'name' => 'Updated Area',
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('number', $validator->errors()->toArray());
        $this->assertArrayHasKey('boundary_geojson', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when number is already used by another area.
     */
    public function test_validation_fails_when_number_is_already_used_by_another_area(): void
    {
        $area1 = Area::factory()->create([
            'number' => 'A123',
        ]);
        
        $area2 = Area::factory()->create([
            'number' => 'B456',
        ]);
        
        $request = new UpdateAreaRequest();
        $request->setRouteResolver(function () use ($area2) {
            $route = new \Illuminate\Routing\Route('PUT', 'admin/areas/{area}', []);
            $route->parameters = ['area' => $area2->id];
            return $route;
        });
        
        $validator = Validator::make([
            'number' => 'A123', // Already used by area1
            'name' => 'Updated Area',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('number', $validator->errors()->toArray());
    }
}
