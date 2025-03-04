<?php
namespace AI\InventoryOptimizer\Api\Data;

interface FraudCheckResultInterface
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
    public function getFraudRisk();
    
    /**
     * @param string $risk
     * @return $this
     */
    public function setFraudRisk($risk);
    
    /**
     * @return float
     */
    public function getRiskScore();
    
    /**
     * @param float $score
     * @return $this
     */
    public function setRiskScore($score);
    
    /**
     * @return string
     */
    public function getAction();
    
    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action);
} 