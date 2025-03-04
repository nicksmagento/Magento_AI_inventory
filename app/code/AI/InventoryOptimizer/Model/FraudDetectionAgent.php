<?php
namespace AI\InventoryOptimizer\Model;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;

class FraudDetectionAgent
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;
    }
    
    /**
     * Check order for potential fraud
     *
     * @param string $orderId
     * @return array
     */
    public function checkOrderForFraud($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            
            // Get order data for fraud analysis
            $orderData = [
                'order_id' => $order->getIncrementId(),
                'customer_ip' => $order->getRemoteIp(),
                'order_value' => $order->getGrandTotal(),
                'payment_method' => $order->getPayment()->getMethod(),
                'billing_address' => $this->formatAddress($order->getBillingAddress()),
                'shipping_address' => $this->formatAddress($order->getShippingAddress())
            ];
            
            // Analyze for fraud risk
            $fraudResult = $this->analyzeFraudRisk($orderData);
            
            // Take action based on fraud risk
            if ($fraudResult['fraud_risk'] === 'High') {
                // Hold or cancel the order
                $this->holdOrCancelOrder($order, $fraudResult);
                $this->logger->warning(sprintf(
                    'AI Fraud Detection: High risk order %s held for review. Risk score: %s',
                    $orderId,
                    $fraudResult['risk_score']
                ));
            }
            
            return $fraudResult;
        } catch (\Exception $e) {
            $this->logger->error('AI Fraud Detection Error: ' . $e->getMessage());
            return [
                'order_id' => $orderId,
                'fraud_risk' => 'Error',
                'action' => 'Manual review required',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format address for fraud analysis
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return array
     */
    private function formatAddress($address)
    {
        if (!$address) {
            return [];
        }
        
        return [
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode()
        ];
    }
    
    /**
     * Analyze order data for fraud risk
     *
     * @param array $orderData
     * @return array
     */
    private function analyzeFraudRisk($orderData)
    {
        // This would integrate with an AI model for fraud detection
        // For now, this is a placeholder implementation
        
        $riskScore = 0;
        
        // Example risk factors
        if ($orderData['order_value'] > 1000) {
            $riskScore += 20;
        }
        
        if ($orderData['billing_address'] && $orderData['shipping_address']) {
            if ($orderData['billing_address']['country_id'] !== $orderData['shipping_address']['country_id']) {
                $riskScore += 30;
            }
        }
        
        // Determine risk level
        $riskLevel = 'Low';
        if ($riskScore > 50) {
            $riskLevel = 'High';
        } elseif ($riskScore > 30) {
            $riskLevel = 'Medium';
        }
        
        $action = 'Process normally';
        if ($riskLevel === 'High') {
            $action = 'Hold for manual review';
        } elseif ($riskLevel === 'Medium') {
            $action = 'Flag for monitoring';
        }
        
        return [
            'order_id' => $orderData['order_id'],
            'fraud_risk' => $riskLevel,
            'risk_score' => $riskScore,
            'action' => $action
        ];
    }
    
    /**
     * Hold or cancel an order based on fraud result
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $fraudResult
     * @return void
     */
    private function holdOrCancelOrder($order, $fraudResult)
    {
        if ($fraudResult['action'] === 'Hold for manual review') {
            $order->hold();
            $this->orderRepository->save($order);
        } elseif ($fraudResult['action'] === 'Cancel') {
            $this->orderManagement->cancel($order->getEntityId());
        }
    }
} 