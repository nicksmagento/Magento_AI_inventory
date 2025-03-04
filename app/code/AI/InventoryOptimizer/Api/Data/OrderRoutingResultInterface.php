<?php
namespace AI\InventoryOptimizer\Api\Data;

interface OrderRoutingResultInterface
{
    /**
     * @return string
     */
    public function getOrderId();
    
    /**
     * @param string $orderId
     * @return $this
     */
    public function setOrderId($orderId);
    
    /**
     * @return string
     */
    public function getAssignedWarehouse();
    
    /**
     * @param string $warehouse
     * @return $this
     */
    public function setAssignedWarehouse($warehouse);
    
    /**
     * @return string
     */
    public function getWarehouseName();
    
    /**
     * @param string $name
     * @return $this
     */
    public function setWarehouseName($name);
    
    /**
     * @return string
     */
    public function getEstimatedDelivery();
    
    /**
     * @param string $delivery
     * @return $this
     */
    public function setEstimatedDelivery($delivery);
} 