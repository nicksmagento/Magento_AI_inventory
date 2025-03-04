<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\FraudDetectionServiceInterface;
use AI\InventoryOptimizer\Api\Data\FraudCheckResultInterfaceFactory;
use AI\InventoryOptimizer\Api\FraudCheckRepositoryInterface;
use AI\InventoryOptimizer\Model\FraudCheckFactory;
use AI\InventoryOptimizer\Model\Service\AiService;
use AI\InventoryOptimizer\Helper\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class FraudDetectionService implements FraudDetectionServiceInterface
{
    /**
     * @var AiService
     */
    private $aiService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var FraudCheckResultInterfaceFactory
     */
    private $fraudCheckResultFactory;
    
    /**
     * @var FraudCheckRepositoryInterface
     */
    private $fraudCheckRepository;
    
    /**
     * @var FraudCheckFactory
     */
    private $fraudCheckFactory;
    
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
     * @param AiService $aiService
     * @param Config $config
     * @param FraudCheckResultInterfaceFactory $fraudCheckResultFactory
     * @param FraudCheckRepositoryInterface $fraudCheckRepository
     * @param FraudCheckFactory $fraudCheckFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        AiService $aiService,
        Config $config,
        FraudCheckResultInterfaceFactory $fraudCheckResultFactory,
        FraudCheckRepositoryInterface $fraudCheckRepository,
        FraudCheckFactory $fraudCheckFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger
    ) {
        $this->aiService = $aiService;
        $this->config = $config;
        $this->fraudCheckResultFactory = $fraudCheckResultFactory;
        $this->fraudCheckRepository = $fraudCheckRepository;
        $this->fraudCheckFactory = $fraudCheckFactory;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;
    }
    
    /**
     * Check order for potential fraud
     *
     * @param string $orderId
     * @param string $customerIp
     * @param float $orderValue
     * @return \AI\InventoryOptimizer\Api\Data\FraudCheckResultInterface
     */
    public function checkOrderForFraud($orderId, $customerIp, $orderValue)
    {
        try {
            if (!$this->config->isFraudDetectionEnabled()) {
                throw new \Exception('AI Fraud Detection is disabled in configuration');
            }
            
            // Prepare order data for AI service
            $orderData = [
                'order_id' => $orderId,
                'customer_ip' => $customerIp,
                'order_value' => $orderValue
            ];
            
            // Call AI service to analyze fraud risk
            $fraudResult = $this->aiService->analyzeFraudRisk($orderData);
            
            // Save fraud check result to database
            $fraudCheck = $this->fraudCheckFactory->create();
            $fraudCheck->setOrderId($orderId);
            $fraudCheck->setFraudRisk($fraudResult['fraud_risk']);
            $fraudCheck->setRiskScore($fraudResult['risk_score']);
            $fraudCheck->setAction($fraudResult['action']);
            $this->fraudCheckRepository->save($fraudCheck);
            
            // Take action based on fraud risk if auto-hold is enabled
            if ($this->config->isAutoHoldEnabled() && $fraudResult['fraud_risk'] === 'High') {
                try {
                    $order = $this->orderRepository->get($orderId);
                    $order->hold();
                    $this->orderRepository->save($order);
                    $this->logger->info(sprintf(
                        'AI Fraud Detection: Order %s held due to high fraud risk (score: %s)',
                        $orderId,
                        $fraudResult['risk_score']
                    ));
                } catch (\Exception $e) {
                    $this->logger->error('Error holding order: ' . $e->getMessage());
                }
            }
            
            // Create and return fraud check result object
            $result = $this->fraudCheckResultFactory->create();
            $result->setOrderId($fraudResult['order_id']);
            $result->setFraudRisk($fraudResult['fraud_risk']);
            $result->setRiskScore($fraudResult['risk_score']);
            $result->setAction($fraudResult['action']);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Fraud Detection Service Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get fraud check result for an order
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Api\Data\FraudCheckResultInterface
     */
    public function getFraudCheckResult($orderId)
    {
        try {
            // Try to get existing fraud check result
            try {
                $fraudCheck = $this->fraudCheckRepository->getByOrderId($orderId);
                
                // Create fraud check result from existing data
                $result = $this->fraudCheckResultFactory->create();
                $result->setOrderId($fraudCheck->getOrderId());
                $result->setFraudRisk($fraudCheck->getFraudRisk());
                $result->setRiskScore($fraudCheck->getRiskScore());
                $result->setAction($fraudCheck->getAction());
                
                return $result;
            } catch (NoSuchEntityException $e) {
                // If no fraud check exists, generate a new one
                $order = $this->orderRepository->get($orderId);
                
                return $this->checkOrderForFraud(
                    $orderId,
                    $order->getRemoteIp(),
                    $order->getGrandTotal()
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Get Fraud Check Result Error: ' . $e->getMessage());
            throw $e;
        }
    }
} 