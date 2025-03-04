<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\ForecastingServiceInterface;
use AI\InventoryOptimizer\Api\Data\ForecastResultInterfaceFactory;
use AI\InventoryOptimizer\Api\Data\ReorderSuggestionInterfaceFactory;
use AI\InventoryOptimizer\Model\Service\AiService;
use AI\InventoryOptimizer\Helper\Config;
use Psr\Log\LoggerInterface;

class ForecastingService implements ForecastingServiceInterface
{
    /**
     * @var AiService
     */
    private $aiService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var ForecastResultInterfaceFactory
     */
    private $forecastResultFactory;
    
    /**
     * @var ReorderSuggestionInterfaceFactory
     */
    private $reorderSuggestionFactory;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param AiService $aiService
     * @param Config $config
     * @param ForecastResultInterfaceFactory $forecastResultFactory
     * @param ReorderSuggestionInterfaceFactory $reorderSuggestionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        AiService $aiService,
        Config $config,
        ForecastResultInterfaceFactory $forecastResultFactory,
        ReorderSuggestionInterfaceFactory $reorderSuggestionFactory,
        LoggerInterface $logger
    ) {
        $this->aiService = $aiService;
        $this->config = $config;
        $this->forecastResultFactory = $forecastResultFactory;
        $this->reorderSuggestionFactory = $reorderSuggestionFactory;
        $this->logger = $logger;
    }
    
    /**
     * Generate demand forecast for a specific SKU
     *
     * @param string $sku
     * @param array $historicalSales
     * @param array $seasonalityData
     * @return \AI\InventoryOptimizer\Api\Data\ForecastResultInterface
     */
    public function generateForecast($sku, array $historicalSales, array $seasonalityData)
    {
        try {
            if (!$this->config->isForecastingEnabled()) {
                throw new \Exception('AI Forecasting is disabled in configuration');
            }
            
            // Call AI service to generate forecast
            $forecastData = $this->aiService->generateForecast($sku, $historicalSales, $seasonalityData);
            
            // Create and return forecast result object
            $forecastResult = $this->forecastResultFactory->create();
            $forecastResult->setSku($forecastData['sku']);
            $forecastResult->setForecastedDemand($forecastData['forecasted_demand']);
            $forecastResult->setSuggestedReorderQty($forecastData['suggested_reorder_qty']);
            $forecastResult->setNextReorderDate($forecastData['next_reorder_date']);
            
            return $forecastResult;
        } catch (\Exception $e) {
            $this->logger->error('Forecasting Service Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get reorder suggestions based on forecasts
     *
     * @param string $sku
     * @return \AI\InventoryOptimizer\Api\Data\ReorderSuggestionInterface
     */
    public function getReorderSuggestion($sku)
    {
        try {
            // In a real implementation, this would retrieve stored forecast data
            // For now, we'll generate a new forecast
            
            // Get historical sales data (simplified)
            $historicalSales = $this->getHistoricalSalesData($sku);
            
            // Get seasonality data (simplified)
            $seasonalityData = $this->getSeasonalityData();
            
            // Generate forecast
            $forecast = $this->generateForecast($sku, $historicalSales, $seasonalityData);
            
            // Create reorder suggestion
            $reorderSuggestion = $this->reorderSuggestionFactory->create();
            $reorderSuggestion->setSku($sku);
            $reorderSuggestion->setRecommendedQty($forecast->getSuggestedReorderQty());
            $reorderSuggestion->setRecommendedDate($forecast->getNextReorderDate());
            $reorderSuggestion->setForecastedDemand($forecast->getForecastedDemand());
            
            return $reorderSuggestion;
        } catch (\Exception $e) {
            $this->logger->error('Reorder Suggestion Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get historical sales data for a SKU
     *
     * @param string $sku
     * @return array
     */
    private function getHistoricalSalesData($sku)
    {
        // In a real implementation, this would query the sales_order_item table
        // For now, return sample data
        return [
            100, 120, 95, 110, 130, 125, 140, 135, 120, 110, 115, 130
        ];
    }
    
    /**
     * Get seasonality data
     *
     * @return array
     */
    private function getSeasonalityData()
    {
        // In a real implementation, this would calculate or retrieve seasonality factors
        // For now, return sample data (monthly seasonality factors)
        return [
            'jan' => 0.8,
            'feb' => 0.9,
            'mar' => 1.0,
            'apr' => 1.1,
            'may' => 1.2,
            'jun' => 1.3,
            'jul' => 1.2,
            'aug' => 1.1,
            'sep' => 1.0,
            'oct' => 1.1,
            'nov' => 1.3,
            'dec' => 1.5
        ];
    }
} 