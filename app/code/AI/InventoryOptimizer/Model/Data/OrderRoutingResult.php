<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterface;
use Magento\Framework\DataObject;

class OrderRoutingResult extends DataObject implements OrderRoutingResultInterface
{
    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }
    
    /**
     * @param string $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData('order_id', $orderId);
    }
    
    /**
     * @return string
     */
    public function getAssignedWarehouse()
    {
        return $this->getData('assigned_warehouse');
    }
    
    /**
     * @param string $warehouse
     * @return $this
     */
    public function setAssignedWarehouse($warehouse)
    {
        return $this->setData('assigned_warehouse', $warehouse);
    }
    
    /**
     * @return string
     */
    public function getWarehouseName()
    {
        return $this->getData('warehouse_name');
    }
    
    /**
     * @param string $name
     * @return $this
     */
    public function setWarehouseName($name)
    {
        return $this->setData('warehouse_name', $name);
    }
    
    /**
     * @return string
     */
    public function getEstimatedDelivery()
    {
        return $this->getData('estimated_delivery');
    }
    
    /**
     * @param string $delivery
     * @return $this
     */
    public function setEstimatedDelivery($delivery)
    {
        return $this->setData('estimated_delivery', $delivery);
    }
} 