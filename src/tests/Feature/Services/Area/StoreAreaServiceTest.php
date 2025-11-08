<?php

namespace Tests\Feature\Services\Area;

use App\Models\Area;
use App\Services\Area\StoreAreaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAreaServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test area creation with the service.
     */
    public function test_it_creates_an_area(): void
    {
        $service = new StoreAreaService();
        
        $areaData = [
            'number' => 'A123',
            'name' => 'Test Area',
            'boundary_kml' => $this->sampleKml(),
            'center_lat' => 35.1234567,
            'center_lng' => 135.7654321,
            'memo' => 'Test memo',
        ];
        
        $area = $service->execute($areaData);
        
        $this->assertInstanceOf(Area::class, $area);
        $this->assertEquals('A123', $area->number);
        $this->assertEquals('Test Area', $area->name);
        $this->assertDatabaseHas('areas', [
            'number' => 'A123',
            'name' => 'Test Area',
        ]);
    }
    
    /**
     * Test area is created with correct attributes.
     */
    public function test_it_creates_area_with_correct_attributes(): void
    {
        $service = new StoreAreaService();
        
        $areaData = [
            'number' => 'B456',
            'name' => 'Another Area',
            'boundary_kml' => $this->sampleKml(),
            'center_lat' => 35.9876543,
            'center_lng' => 135.1234567,
            'memo' => 'Another memo',
        ];
        
        $area = $service->execute($areaData);
        
        $this->assertEquals('B456', $area->number);
        $this->assertEquals('Another Area', $area->name);
        $this->assertEquals('Another memo', $area->memo);
        $this->assertDatabaseHas('areas', [
            'number' => 'B456',
            'name' => 'Another Area',
            'memo' => 'Another memo',
        ]);
    }
    
    /**
     * Test area creation with minimal data.
     */
    public function test_it_creates_area_with_minimal_data(): void
    {
        $service = new StoreAreaService();
        
        $areaData = [
            'number' => 'C789',
            'boundary_kml' => $this->sampleKml(),
            'center_lat' => 35.0000000,
            'center_lng' => 135.0000000,
        ];
        
        $area = $service->execute($areaData);
        
        $this->assertEquals('C789', $area->number);
        $this->assertNull($area->name);
        $this->assertNull($area->memo);
        $this->assertDatabaseHas('areas', [
            'number' => 'C789',
        ]);
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
