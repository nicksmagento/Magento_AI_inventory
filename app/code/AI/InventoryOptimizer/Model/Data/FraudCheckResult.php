<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\FraudCheckResultInterface;
use Magento\Framework\DataObject;

class FraudCheckResult extends DataObject implements FraudCheckResultInterface
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
    public function getFraudRisk()
    {
        return $this->getData('fraud_risk');
    }
    
    /**
     * @param string $risk
     * @return $this
     */
    public function setFraudRisk($risk)
    {
        return $this->setData('fraud_risk', $risk);
    }
    
    /**
     * @return float
     */
    public function getRiskScore()
    {
        return $this->getData('risk_score');
    }
    
    /**
     * @param float $score
     * @return $this
     */
    public function setRiskScore($score)
    {
        return $this->setData('risk_score', $score);
    }
    
    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getData('action');
    }
    
    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        return $this->setData('action', $action);
    }
} 