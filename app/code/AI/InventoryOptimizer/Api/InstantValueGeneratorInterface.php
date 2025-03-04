<?php
namespace AI\InventoryOptimizer\Api;

interface InstantValueGeneratorInterface
{
    /**
     * Generate instant insights
     *
     * @return bool
     */
    public function generateInstantInsights();
    
    /**
     * Identify potential stockouts
     *
     * @return bool
     */
    public function identifyPotentialStockouts();
    
    /**
     * Identify inventory imbalances
     *
     * @return bool
     */
    public function identifyInventoryImbalances();
    
    /**
     * Identify low safety stock
     *
     * @return bool
     */
    public function identifyLowSafetyStock();
    
    /**
     * Identify seasonal products
     *
     * @return bool
     */
    public function identifySeasonalProducts();
} 