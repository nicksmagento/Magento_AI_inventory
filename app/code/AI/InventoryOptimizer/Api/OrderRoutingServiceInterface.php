<?php
namespace AI\InventoryOptimizer\Api;

interface OrderRoutingServiceInterface
{
    /**
     * Route an order to the optimal fulfillment center
     *
     * @param string $orderId
     * @param string $customerLocation
     * @param string[] $availableWarehouses
     * @param mixed $shippingCosts
     * @return \AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterface
     */
    public function routeOrder($orderId, $customerLocation, array $availableWarehouses, $shippingCosts);
    
    /**
     * Get routing information for an order
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterface
     */
    public function getOrderRouting($orderId);
} 