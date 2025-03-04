<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\ForecastResultInterface;
use Magento\Framework\DataObject;

class ForecastResult extends DataObject implements ForecastResultInterface
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
    
    /**
     * @return int
     */
    public function getSuggestedReorderQty()
    {
        return $this->getData('suggested_reorder_qty');
    }
    
    /**
     * @param int $qty
     * @return $this
     */
    public function setSuggestedReorderQty($qty)
    {
        return $this->setData('suggested_reorder_qty', $qty);
    }
    
    /**
     * @return string
     */
    public function getNextReorderDate()
    {
        return $this->getData('next_reorder_date');
    }
    
    /**
     * @param string $date
     * @return $this
     */
    public function setNextReorderDate($date)
    {
        return $this->setData('next_reorder_date', $date);
    }
} 