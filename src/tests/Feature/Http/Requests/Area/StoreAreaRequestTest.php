<?php

namespace Tests\Feature\Http\Requests\Area;

use App\Http\Requests\Admin\StoreAreaRequest;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreAreaRequestTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test validation passes with valid data.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $request = new StoreAreaRequest();
        
        $validator = Validator::make([
            'number' => 'A123',
            'name' => 'Test Area',
            'boundary_kml' => $this->sampleKml(),
            'memo' => 'Test memo',
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }
    
    /**
     * Test validation fails when required fields are missing.
     */
    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $request = new StoreAreaRequest();
        
        $validator = Validator::make([
            'name' => 'Test Area',
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('number', $validator->errors()->toArray());
        $this->assertArrayHasKey('boundary_kml', $validator->errors()->toArray());
    }
    
    /**
     * Test validation fails when number is not unique.
     */
    public function test_validation_fails_when_number_is_not_unique(): void
    {
        Area::factory()->create([
            'number' => 'A123',
        ]);
        
        $request = new StoreAreaRequest();
        
        $validator = Validator::make([
            'number' => 'A123',
            'name' => 'Test Area',
            'boundary_kml' => $this->sampleKml(),
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('number', $validator->errors()->toArray());
    }
    
    /**
     * Test validation passes with minimal required data.
     */
    public function test_validation_passes_with_minimal_required_data(): void
    {
        $request = new StoreAreaRequest();
        
        $validator = Validator::make([
            'number' => 'B456',
            'boundary_kml' => $this->sampleKml(),
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    private function sampleKml(): string
    {
        return <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Placemark>
    <Polygon>
      <outerBoundaryIs>
        <LinearRing>
          <coordinates>
            139.7671,35.6812,0 139.7681,35.6802,0 139.7661,35.6792,0 139.7671,35.6812,0
          </coordinates>
        </LinearRing>
      </outerBoundaryIs>
    </Polygon>
  </Placemark>
</kml>
KML;
    }
}
