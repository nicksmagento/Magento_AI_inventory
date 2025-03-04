<?php
namespace AI\InventoryOptimizer\Model\Onboarding;

use AI\InventoryOptimizer\Api\Data\SuccessInterface;
use AI\InventoryOptimizer\Api\SuccessRepositoryInterface;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SuccessTracker
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var SuccessRepositoryInterface
     */
    private $successRepository;
    
    /**
     * @var SuccessInterfaceFactory
     */
    private $successFactory;
    
    /**
     * @var AuthSession
     */
    private $authSession;
    
    /**
     * @var NotifierInterface
     */
    private $notifier;
    
    /**
     * @var Json
     */
    private $json;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @param Config $config
     * @param Logger $logger
     * @param SuccessRepositoryInterface $successRepository
     * @param \AI\InventoryOptimizer\Api\Data\SuccessInterfaceFactory $successFactory
     * @param AuthSession $authSession
     * @param NotifierInterface $notifier
     * @param Json $json
     * @param DateTime $dateTime
     */
    public function __construct(
        Config $config,
        Logger $logger,
        SuccessRepositoryInterface $successRepository,
        \AI\InventoryOptimizer\Api\Data\SuccessInterfaceFactory $successFactory,
        AuthSession $authSession,
        NotifierInterface $notifier,
        Json $json,
        DateTime $dateTime
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->successRepository = $successRepository;
        $this->successFactory = $successFactory;
        $this->authSession = $authSession;
        $this->notifier = $notifier;
        $this->json = $json;
        $this->dateTime = $dateTime;
    }
    
    /**
     * Track a success
     *
     * @param string $successType
     * @param string $title
     * @param string $description
     * @param float|null $impactValue
     * @param string|null $impactType
     * @param float|null $timeSaved
     * @param bool $highlight
     * @return SuccessInterface
     */
    public function trackSuccess(
        $successType,
        $title,
        $description,
        $impactValue = null,
        $impactType = null,
        $timeSaved = null,
        $highlight = false
    ) {
        try {
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                $this->logger->warning('Cannot track success: No user is logged in');
                return null;
            }
            
            $success = $this->successFactory->create();
            $success->setUserId($userId);
            $success->setSuccessType($successType);
            $success->setTitle($title);
            $success->setDescription($description);
            
            if ($impactValue !== null) {
                $success->setImpactValue($impactValue);
            }
            
            if ($impactType !== null) {
                $success->setImpactType($impactType);
            }
            
            if ($timeSaved !== null) {
                $success->setTimeSaved($timeSaved);
            }
            
            $success->setIsHighlighted($highlight);
            
            $this->successRepository->save($success);
            
            $this->logger->info(
                sprintf(
                    'Tracked success for user %d: %s',
                    $userId,
                    $title
                )
            );
            
            // If this is a highlighted success, show a notification
            if ($highlight) {
                $this->notifySuccess($title, $description);
            }
            
            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Error tracking success: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Track a recommendation implementation
     *
     * @param string $recommendationType
     * @param array $recommendationData
     * @return SuccessInterface|null
     */
    public function trackRecommendationImplementation($recommendationType, $recommendationData)
    {
        try {
            $title = '';
            $description = '';
            $impactValue = null;
            $impactType = null;
            $timeSaved = null;
            
            switch ($recommendationType) {
                case 'reorder':
                    $sku = $recommendationData['sku'] ?? '';
                    $productName = $recommendationData['product_name'] ?? $sku;
                    $quantity = $recommendationData['quantity'] ?? 0;
                    
                    $title = 'Reorder Recommendation Implemented';
                    $description = sprintf(
                        'You\'ve implemented a reorder recommendation for %s (%s), ordering %d units. ' .
                        'This helps prevent potential stockouts and maintain optimal inventory levels.',
                        $productName,
                        $sku,
                        $quantity
                    );
                    
                    $impactValue = $recommendationData['potential_revenue'] ?? null;
                    $impactType = 'revenue';
                    $timeSaved = 15.0; // 15 minutes saved from manual forecasting
                    break;
                    
                case 'stock_transfer':
                    $sku = $recommendationData['sku'] ?? '';
                    $productName = $recommendationData['product_name'] ?? $sku;
                    $quantity = $recommendationData['quantity'] ?? 0;
                    $sourceWarehouse = $recommendationData['source_warehouse'] ?? '';
                    $destWarehouse = $recommendationData['destination_warehouse'] ?? '';
                    
                    $title = 'Stock Transfer Recommendation Implemented';
                    $description = sprintf(
                        'You\'ve implemented a stock transfer recommendation for %s (%s), ' .
                        'transferring %d units from %s to %s. ' .
                        'This helps balance inventory across warehouses and improve delivery times.',
                        $productName,
                        $sku,
                        $quantity,
                        $sourceWarehouse,
                        $destWarehouse
                    );
                    
                    $impactValue = $recommendationData['delivery_time_impact'] ?? null;
                    $impactType = 'days';
                    $timeSaved = 20.0; // 20 minutes saved from manual analysis
                    break;
                    
                case 'order_routing':
                    $orderId = $recommendationData['order_id'] ?? '';
                    $warehouseCode = $recommendationData['warehouse_code'] ?? '';
                    
                    $title = 'Order Routing Recommendation Implemented';
                    $description = sprintf(
                        'You\'ve implemented an order routing recommendation for order #%s, ' .
                        'routing it through %s. ' .
                        'This helps optimize shipping costs and delivery times.',
                        $orderId,
                        $warehouseCode
                    );
                    
                    $impactValue = $recommendationData['shipping_cost_savings'] ?? null;
                    $impactType = 'savings';
                    $timeSaved = 5.0; // 5 minutes saved from manual routing
                    break;
                    
                case 'fraud_detection':
                    $orderId = $recommendationData['order_id'] ?? '';
                    $riskScore = $recommendationData['risk_score'] ?? 0;
                    
                    $title = 'Fraud Detection Alert Actioned';
                    $description = sprintf(
                        'You\'ve taken action on a fraud detection alert for order #%s, ' .
                        'which had a risk score of %.0f%%. ' .
                        'This helps prevent potential fraud and protect your business.',
                        $orderId,
                        $riskScore * 100
                    );
                    
                    $impactValue = $recommendationData['potential_loss'] ?? null;
                    $impactType = 'savings';
                    $timeSaved = 12.0; // 12 minutes saved from manual review
                    break;
                    
                default:
                    $title = 'AI Recommendation Implemented';
                    $description = 'You\'ve successfully implemented an AI recommendation. ' .
                                  'This helps optimize your inventory management.';
                    $timeSaved = 10.0; // 10 minutes saved as default
            }
            
            return $this->trackSuccess(
                $recommendationType,
                $title,
                $description,
                $impactValue,
                $impactType,
                $timeSaved,
                true // Highlight recommendation implementations
            );
        } catch (\Exception $e) {
            $this->logger->error('Error tracking recommendation implementation: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get total time saved for current user
     *
     * @return float
     */
    public function getTotalTimeSaved()
    {
        try {
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                return 0;
            }
            
            return $this->successRepository->getTotalTimeSavedByUser($userId);
        } catch (\Exception $e) {
            $this->logger->error('Error getting total time saved: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total impact value for current user by impact type
     *
     * @param string $impactType
     * @return float
     */
    public function getTotalImpactValue($impactType)
    {
        try {
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                return 0;
            }
            
            return $this->successRepository->getTotalImpactValueByUser($userId, $impactType);
        } catch (\Exception $e) {
            $this->logger->error('Error getting total impact value: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get recent successes for current user
     *
     * @param int $limit
     * @return SuccessInterface[]
     */
    public function getRecentSuccesses($limit = 5)
    {
        try {
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                return [];
            }
            
            return $this->successRepository->getRecentByUser($userId, $limit);
        } catch (\Exception $e) {
            $this->logger->error('Error getting recent successes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get current user ID
     *
     * @return int|null
     */
    private function getCurrentUserId()
    {
        $user = $this->authSession->getUser();
        return $user ? $user->getId() : null;
    }
    
    /**
     * Show success notification
     *
     * @param string $title
     * @param string $description
     * @return void
     */
    private function notifySuccess($title, $description)
    {
        try {
            $this->notifier->addNotice(
                $title,
                $description
            );
        } catch (\Exception $e) {
            $this->logger->error('Error showing success notification: ' . $e->getMessage());
        }
    }
} 