<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\StockTransferInterface;
use Magento\Framework\DataObject;

class StockTransfer extends DataObject implements StockTransferInterface
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
    public function getTransferQty()
    {
        return $this->getData('transfer_qty');
    }
    
    /**
     * @param int $qty
     * @return $this
     */
    public function setTransferQty($qty)
    {
        return $this->setData('transfer_qty', $qty);
    }
    
    /**
     * @return string
     */
    public function getFromWarehouse()
    {
        return $this->getData('from_warehouse');
    }
    
    /**
     * @param string $warehouse
     * @return $this
     */
    public function setFromWarehouse($warehouse)
    {
        return $this->setData('from_warehouse', $warehouse);
    }
    
    /**
     * @return string
     */
    public function getToWarehouse()
    {
        return $this->getData('to_warehouse');
    }
    
    /**
     * @param string $warehouse
     * @return $this
     */
    public function setToWarehouse($warehouse)
    {
        return $this->setData('to_warehouse', $warehouse);
    }
} 