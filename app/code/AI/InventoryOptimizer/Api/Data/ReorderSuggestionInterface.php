<?php
namespace AI\InventoryOptimizer\Api\Data;

interface ReorderSuggestionInterface
{
    /**
     * @return string
     */
    public function getSku();
    
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);
    
    /**
     * @return int
     */
    public function getRecommendedQty();
    
    /**
     * @param int $qty
     * @return $this
     */
    public function setRecommendedQty($qty);
    
    /**
     * @return string
     */
    public function getRecommendedDate();
    
    /**
     * @param string $date
     * @return $this
     */
    public function setRecommendedDate($date);
    
    /**
     * @return int
     */
    public function getForecastedDemand();
    
    /**
     * @param int $demand
     * @return $this
     */
    public function setForecastedDemand($demand);
} 