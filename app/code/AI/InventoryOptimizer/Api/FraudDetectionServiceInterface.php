<?php
namespace AI\InventoryOptimizer\Api;

interface FraudDetectionServiceInterface
{
    /**
     * Check order for potential fraud
     *
     * @param string $orderId
     * @param string $customerIp
     * @param float $orderValue
     * @return \AI\InventoryOptimizer\Api\Data\FraudCheckResultInterface
     */
    public function checkOrderForFraud($orderId, $customerIp, $orderValue);
    
    /**
     * Get fraud check result for an order
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Api\Data\FraudCheckResultInterface
     */
    public function getFraudCheckResult($orderId);
} 