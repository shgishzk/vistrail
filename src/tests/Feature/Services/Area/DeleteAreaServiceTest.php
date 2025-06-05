<?php

namespace Tests\Feature\Services\Area;

use App\Models\Area;
use App\Services\Area\DeleteAreaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAreaServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test area deletion with the service.
     */
    public function test_it_deletes_an_area(): void
    {
        $area = Area::factory()->create();
        
        $service = new DeleteAreaService();
        
        $result = $service->execute($area);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('areas', [
            'id' => $area->id,
        ]);
    }
    
    /**
     * Test service returns false if deletion fails.
     */
    public function test_it_returns_false_if_deletion_fails(): void
    {
        $mockArea = $this->createMock(Area::class);
        $mockArea->method('delete')->willReturn(false);
        
        $service = new DeleteAreaService();
        
        $result = $service->execute($mockArea);
        
        $this->assertFalse($result);
    }
}
