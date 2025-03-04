<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Model\Communication\LanguageProcessor;
use AI\InventoryOptimizer\Model\Onboarding\SuccessTracker;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\Event\ManagerInterface as EventManager;

class OrderPlaced implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var LanguageProcessor
     */
    private $languageProcessor;
    
    /**
     * @var SuccessTracker
     */
    private $successTracker;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var EventManager
     */
    private $eventManager;
    
    /**
     * @param Config $config
     * @param LanguageProcessor $languageProcessor
     * @param SuccessTracker $successTracker
     * @param Logger $logger
     * @param EventManager $eventManager
     */
    public function __construct(
        Config $config,
        LanguageProcessor $languageProcessor,
        SuccessTracker $successTracker,
        Logger $logger,
        EventManager $eventManager
    ) {
        $this->config = $config;
        $this->languageProcessor = $languageProcessor;
        $this->successTracker = $successTracker;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }
    
    /**
     * Observer execution
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isModuleEnabled()) {
            return;
        }
        
        try {
            $order = $observer->getEvent()->getOrder();
            
            // Track if this order was placed based on an AI recommendation
            $aiRecommendationId = $order->getData('ai_recommendation_id');
            if ($aiRecommendationId) {
                $this->successTracker->trackRecommendationImplementation(
                    $aiRecommendationId,
                    'order_placed',
                    $order->getIncrementId(),
                    $order->getGrandTotal()
                );
                
                $this->logger->info('Tracked order placement from AI recommendation: ' . $order->getIncrementId());
            }
            
            // Dispatch custom event for AI agents to process
            $this->eventManager->dispatch(
                'ai_inventory_order_placed',
                ['order' => $order]
            );
        } catch (\Exception $e) {
            $this->logger->error('Error processing order placed event: ' . $e->getMessage());
        }
    }
} 