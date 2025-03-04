<?php
namespace AI\InventoryOptimizer\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use AI\InventoryOptimizer\Api\ForecastingServiceInterface;
use AI\InventoryOptimizer\Api\Data\ForecastResultInterface;

class ForecastingServiceTest extends TestCase
{
    /**
     * @var ForecastingServiceInterface
     */
    private $forecastingService;
    
    protected function setUp(): void
    {
        $this->forecastingService = Bootstrap::getObjectManager()->get(ForecastingServiceInterface::class);
    }
    
    /**
     * @magentoConfigFixture current_store ai_inventory/general/enabled 1
     * @magentoConfigFixture current_store ai_inventory/forecasting/enabled 1
     */
    public function testGenerateForecast()
    {
        $sku = 'test-product';
        $days = 30;
        
        $result = $this->forecastingService->generateForecast($sku, $days);
        
        $this->assertInstanceOf(ForecastResultInterface::class, $result);
        $this->assertEquals($sku, $result->getSku());
        $this->assertNotEmpty($result->getForecastData());
    }
    
    /**
     * @magentoConfigFixture current_store ai_inventory/general/enabled 1
     * @magentoConfigFixture current_store ai_inventory/forecasting/enabled 1
     */
    public function testGenerateReorderSuggestions()
    {
        $result = $this->forecastingService->generateReorderSuggestions();
        
        $this->assertIsArray($result);
        
        if (count($result) > 0) {
            $suggestion = $result[0];
            $this->assertNotEmpty($suggestion->getSku());
            $this->assertGreaterThan(0, $suggestion->getReorderQuantity());
        }
    }
} 