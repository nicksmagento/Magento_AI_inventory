<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\FraudDetectionAgent;
use Psr\Log\LoggerInterface;

class PaymentProcessObserver implements ObserverInterface
{
    /**
     * @var FraudDetectionAgent
     */
    private $fraudDetectionAgent;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param FraudDetectionAgent $fraudDetectionAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        FraudDetectionAgent $fraudDetectionAgent,
        LoggerInterface $logger
    ) {
        $this->fraudDetectionAgent = $fraudDetectionAgent;
        $this->logger = $logger;
    }
    
    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $payment = $observer->getEvent()->getPayment();
            
            if (!$payment) {
                return;
            }
            
            $order = $payment->getOrder();
            
            // Check order for potential fraud
            $fraudResult = $this->fraudDetectionAgent->checkOrderForFraud($order->getId());
            
            $this->logger->info(sprintf(
                'AI Fraud Detection: Checked order %s, risk level: %s',
                $order->getIncrementId(),
                $fraudResult['fraud_risk']
            ));
        } catch (\Exception $e) {
            $this->logger->error('AI Fraud Detection Observer Error: ' . $e->getMessage());
        }
    }
} 