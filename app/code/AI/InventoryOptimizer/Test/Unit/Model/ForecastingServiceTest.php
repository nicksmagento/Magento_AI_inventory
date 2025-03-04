<?php
namespace AI\InventoryOptimizer\Test\Unit\Model;

use AI\InventoryOptimizer\Model\ForecastingService;
use AI\InventoryOptimizer\Model\Service\AiService;
use AI\InventoryOptimizer\Helper\Config;
use AI\InventoryOptimizer\Api\Data\ForecastResultInterface;
use AI\InventoryOptimizer\Api\Data\ForecastResultInterfaceFactory;
use AI\InventoryOptimizer\Api\Data\ReorderSuggestionInterface;
use AI\InventoryOptimizer\Api\Data\ReorderSuggestionInterfaceFactory;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ForecastingServiceTest extends TestCase
{
    /**
     * @var ForecastingService
     */
    private $forecastingService;
    
    /**
     * @var AiService|MockObject
     */
    private $aiServiceMock;
    
    /**
     * @var Config|MockObject
     */
    private $configMock;
    
    /**
     * @var ForecastResultInterfaceFactory|MockObject
     */
    private $forecastResultFactoryMock;
    
    /**
     * @var ReorderSuggestionInterfaceFactory|MockObject
     */
    private $reorderSuggestionFactoryMock;
    
    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;
    
    /**
     * @var ForecastResultInterface|MockObject
     */
    private $forecastResultMock;
    
    /**
     * @var ReorderSuggestionInterface|MockObject
     */
    private $reorderSuggestionMock;
    
    protected function setUp(): void
    {
        $this->aiServiceMock = $this->createMock(AiService::class);
        $this->configMock = $this->createMock(Config::class);
        $this->forecastResultFactoryMock = $this->createMock(ForecastResultInterfaceFactory::class);
        $this->reorderSuggestionFactoryMock = $this->createMock(ReorderSuggestionInterfaceFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        
        $this->forecastResultMock = $this->createMock(ForecastResultInterface::class);
        $this->reorderSuggestionMock = $this->createMock(ReorderSuggestionInterface::class);
        
        $this->forecastingService = new ForecastingService(
            $this->aiServiceMock,
            $this->configMock,
            $this->forecastResultFactoryMock,
            $this->reorderSuggestionFactoryMock,
            $this->loggerMock
        );
    }
    
    public function testGenerateForecastWhenEnabled()
    {
        $sku = 'test-sku';
        $historicalSales = [100, 120, 95, 110];
        $seasonalityData = ['jan' => 0.8, 'feb' => 0.9];
        
        $forecastData = [
            'sku' => $sku,
            'forecasted_demand' => 150,
            'suggested_reorder_qty' => 100,
            'next_reorder_date' => '2023-12-31'
        ];
        
        // Configure mocks
        $this->configMock->expects($this->once())
            ->method('isForecastingEnabled')
            ->willReturn(true);
            
        $this->aiServiceMock->expects($this->once())
            ->method('generateForecast')
            ->with($sku, $historicalSales, $seasonalityData)
            ->willReturn($forecastData);
            
        $this->forecastResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->forecastResultMock);
            
        $this->forecastResultMock->expects($this->once())
            ->method('setSku')
            ->with($forecastData['sku'])
            ->willReturnSelf();
            
        $this->forecastResultMock->expects($this->once())
            ->method('setForecastedDemand')
            ->with($forecastData['forecasted_demand'])
            ->willReturnSelf();
            
        $this->forecastResultMock->expects($this->once())
            ->method('setSuggestedReorderQty')
            ->with($forecastData['suggested_reorder_qty'])
            ->willReturnSelf();
            
        $this->forecastResultMock->expects($this->once())
            ->method('setNextReorderDate')
            ->with($forecastData['next_reorder_date'])
            ->willReturnSelf();
        
        // Execute the method
        $result = $this->forecastingService->generateForecast($sku, $historicalSales, $seasonalityData);
        
        // Assert the result
        $this->assertSame($this->forecastResultMock, $result);
    }
    
    public function testGenerateForecastWhenDisabled()
    {
        $sku = 'test-sku';
        $historicalSales = [100, 120, 95, 110];
        $seasonalityData = ['jan' => 0.8, 'feb' => 0.9];
        
        // Configure mocks
        $this->configMock->expects($this->once())
            ->method('isForecastingEnabled')
            ->willReturn(false);
            
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('AI Forecasting is disabled in configuration');
        
        // Execute the method
        $this->forecastingService->generateForecast($sku, $historicalSales, $seasonalityData);
    }
    
    public function testGetReorderSuggestion()
    {
        $sku = 'test-sku';
        $historicalSales = [100, 120, 95, 110];
        $seasonalityData = ['jan' => 0.8, 'feb' => 0.9];
        
        $forecastData = [
            'sku' => $sku,
            'forecasted_demand' => 150,
            'suggested_reorder_qty' => 100,
            'next_reorder_date' => '2023-12-31'
        ];
        
        // Configure mocks for the private methods using reflection
        $forecastingServiceMock = $this->getMockBuilder(ForecastingService::class)
            ->setConstructorArgs([
                $this->aiServiceMock,
                $this->configMock,
                $this->forecastResultFactoryMock,
                $this->reorderSuggestionFactoryMock,
                $this->loggerMock
            ])
            ->onlyMethods(['getHistoricalSalesData', 'getSeasonalityData', 'generateForecast'])
            ->getMock();
            
        $forecastingServiceMock->expects($this->once())
            ->method('getHistoricalSalesData')
            ->with($sku)
            ->willReturn($historicalSales);
            
        $forecastingServiceMock->expects($this->once())
            ->method('getSeasonalityData')
            ->willReturn($seasonalityData);
            
        $forecastingServiceMock->expects($this->once())
            ->method('generateForecast')
            ->with($sku, $historicalSales, $seasonalityData)
            ->willReturn($this->forecastResultMock);
            
        $this->forecastResultMock->expects($this->once())
            ->method('getSuggestedReorderQty')
            ->willReturn($forecastData['suggested_reorder_qty']);
            
        $this->forecastResultMock->expects($this->once())
            ->method('getNextReorderDate')
            ->willReturn($forecastData['next_reorder_date']);
            
        $this->forecastResultMock->expects($this->once())
            ->method('getForecastedDemand')
            ->willReturn($forecastData['forecasted_demand']);
            
        $this->reorderSuggestionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->reorderSuggestionMock);
            
        $this->reorderSuggestionMock->expects($this->once())
            ->method('setSku')
            ->with($sku)
            ->willReturnSelf();
            
        $this->reorderSuggestionMock->expects($this->once())
            ->method('setRecommendedQty')
            ->with($forecastData['suggested_reorder_qty'])
            ->willReturnSelf();
            
        $this->reorderSuggestionMock->expects($this->once())
            ->method('setRecommendedDate')
            ->with($forecastData['next_reorder_date'])
            ->willReturnSelf();
            
        $this->reorderSuggestionMock->expects($this->once())
            ->method('setForecastedDemand')
            ->with($forecastData['forecasted_demand'])
            ->willReturnSelf();
        
        // Execute the method
        $result = $forecastingServiceMock->getReorderSuggestion($sku);
        
        // Assert the result
        $this->assertSame($this->reorderSuggestionMock, $result);
    }
} 