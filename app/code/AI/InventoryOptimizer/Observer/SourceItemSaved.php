<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Model\MagicMoments\OpportunityDetector;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\Event\ManagerInterface as EventManager;

class SourceItemSaved implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var OpportunityDetector
     */
    private $opportunityDetector;
    
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
     * @param OpportunityDetector $opportunityDetector
     * @param Logger $logger
     * @param EventManager $eventManager
     */
    public function __construct(
        Config $config,
        OpportunityDetector $opportunityDetector,
        Logger $logger,
        EventManager $eventManager
    ) {
        $this->config = $config;
        $this->opportunityDetector = $opportunityDetector;
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
        if (!$this->config->isModuleEnabled() || !$this->config->isMagicMomentsEnabled()) {
            return;
        }
        
        try {
            $sourceItem = $observer->getEvent()->getSourceItem();
            
            // Check if inventory level has changed significantly
            $oldQty = $sourceItem->getOrigData('quantity');
            $newQty = $sourceItem->getQuantity();
            
            // If inventory has dropped significantly, check for opportunities
            if ($oldQty && $newQty && ($newQty < $oldQty * 0.5)) {
                // Schedule opportunity detection for this product
                $this->opportunityDetector->detectCompetitorStockouts([$sourceItem->getSku()]);
                
                $this->logger->info('Triggered opportunity detection for SKU with significant inventory change: ' . $sourceItem->getSku());
            }
            
            // Dispatch custom event for AI agents to process
            $this->eventManager->dispatch(
                'ai_inventory_source_item_saved',
                ['source_item' => $sourceItem]
            );
        } catch (\Exception $e) {
            $this->logger->error('Error processing source item saved event: ' . $e->getMessage());
        }
    }
} 