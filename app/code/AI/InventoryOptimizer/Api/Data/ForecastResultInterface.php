<?php
namespace AI\InventoryOptimizer\Api\Data;

interface ForecastResultInterface
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
    public function getForecastedDemand();
    
    /**
     * @param int $demand
     * @return $this
     */
    public function setForecastedDemand($demand);
    
    /**
     * @return int
     */
    public function getSuggestedReorderQty();
    
    /**
     * @param int $qty
     * @return $this
     */
    public function setSuggestedReorderQty($qty);
    
    /**
     * @return string
     */
    public function getNextReorderDate();
    
    /**
     * @param string $date
     * @return $this
     */
    public function setNextReorderDate($date);
} 