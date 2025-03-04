<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\ReorderSuggestionInterface;
use Magento\Framework\DataObject;

class ReorderSuggestion extends DataObject implements ReorderSuggestionInterface
{
    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getData('sku');
    }
    
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData('sku', $sku);
    }
    
    /**
     * @return int
     */
    public function getRecommendedQty()
    {
        return $this->getData('recommended_qty');
    }
    
    /**
     * @param int $qty
     * @return $this
     */
    public function setRecommendedQty($qty)
    {
        return $this->setData('recommended_qty', $qty);
    }
    
    /**
     * @return string
     */
    public function getRecommendedDate()
    {
        return $this->getData('recommended_date');
    }
    
    /**
     * @param string $date
     * @return $this
     */
    public function setRecommendedDate($date)
    {
        return $this->setData('recommended_date', $date);
    }
    
    /**
     * @return int
     */
    public function getForecastedDemand()
    {
        return $this->getData('forecasted_demand');
    }
    
    /**
     * @param int $demand
     * @return $this
     */
    public function setForecastedDemand($demand)
    {
        return $this->setData('forecasted_demand', $demand);
    }
} 