<?php

namespace Tests\Feature\Services\Area;

use App\Models\Area;
use App\Services\Area\UpdateAreaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAreaServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test area update with the service.
     */
    public function test_it_updates_an_area(): void
    {
        $area = Area::factory()->create([
            'number' => 'A123',
            'name' => 'Original Area',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
            'memo' => 'Original memo',
        ]);
        
        $service = new UpdateAreaService();
        
        $areaData = [
            'number' => 'B456',
            'name' => 'Updated Area',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
            'memo' => 'Updated memo',
        ];
        
        $updatedArea = $service->execute($area, $areaData);
        
        $this->assertInstanceOf(Area::class, $updatedArea);
        $this->assertEquals('B456', $updatedArea->number);
        $this->assertEquals('Updated Area', $updatedArea->name);
        $this->assertEquals('Updated memo', $updatedArea->memo);
        $this->assertDatabaseHas('areas', [
            'id' => $area->id,
            'number' => 'B456',
            'name' => 'Updated Area',
            'memo' => 'Updated memo',
        ]);
    }
    
    /**
     * Test area is updated with correct attributes.
     */
    public function test_it_updates_area_with_correct_attributes(): void
    {
        $area = Area::factory()->create();
        
        $service = new UpdateAreaService();
        
        $areaData = [
            'number' => 'C789',
            'name' => 'New Name',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
            'memo' => 'New memo',
        ];
        
        $updatedArea = $service->execute($area, $areaData);
        
        $this->assertEquals('C789', $updatedArea->number);
        $this->assertEquals('New Name', $updatedArea->name);
        $this->assertEquals('New memo', $updatedArea->memo);
        $this->assertDatabaseHas('areas', [
            'id' => $area->id,
            'number' => 'C789',
            'name' => 'New Name',
            'memo' => 'New memo',
        ]);
    }
    
    /**
     * Test partial update of an area.
     */
    public function test_it_updates_area_partially(): void
    {
        $area = Area::factory()->create([
            'number' => 'D123',
            'name' => 'Original Name',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
            'memo' => 'Original memo',
        ]);
        
        $service = new UpdateAreaService();
        
        $areaData = [
            'number' => 'D123', // Same number
            'name' => 'Updated Name',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[139.7671,35.6812],[139.7681,35.6802],[139.7661,35.6792],[139.7671,35.6812]]]}',
            'memo' => 'Original memo', // Same memo
        ];
        
        $updatedArea = $service->execute($area, $areaData);
        
        $this->assertEquals('D123', $updatedArea->number);
        $this->assertEquals('Updated Name', $updatedArea->name);
        $this->assertEquals('Original memo', $updatedArea->memo);
        $this->assertDatabaseHas('areas', [
            'id' => $area->id,
            'number' => 'D123',
            'name' => 'Updated Name',
            'memo' => 'Original memo',
        ]);
    }
}
