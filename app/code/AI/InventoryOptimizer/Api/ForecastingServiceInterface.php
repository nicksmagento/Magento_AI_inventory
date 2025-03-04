<?php
namespace AI\InventoryOptimizer\Api;

interface ForecastingServiceInterface
{
    /**
     * Generate demand forecast for a specific SKU
     *
     * @param string $sku
     * @param array $historicalSales
     * @param array $seasonalityData
     * @return \AI\InventoryOptimizer\Api\Data\ForecastResultInterface
     */
    public function generateForecast($sku, array $historicalSales, array $seasonalityData);
    
    /**
     * Get reorder suggestions based on forecasts
     *
     * @param string $sku
     * @return \AI\InventoryOptimizer\Api\Data\ReorderSuggestionInterface
     */
    public function getReorderSuggestion($sku);
} 