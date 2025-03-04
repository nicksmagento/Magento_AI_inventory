<?php
namespace AI\InventoryOptimizer\Api\Data;

interface StockTransferInterface
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
    public function getTransferQty();
    
    /**
     * @param int $qty
     * @return $this
     */
    public function setTransferQty($qty);
    
    /**
     * @return string
     */
    public function getFromWarehouse();
    
    /**
     * @param string $warehouse
     * @return $this
     */
    public function setFromWarehouse($warehouse);
    
    /**
     * @return string
     */
    public function getToWarehouse();
    
    /**
     * @param string $warehouse
     * @return $this
     */
    public function setToWarehouse($warehouse);
} 