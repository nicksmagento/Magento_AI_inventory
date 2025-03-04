<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\Event\ManagerInterface as EventManager;

class ProductSaved implements ObserverInterface
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
     * @var EventManager
     */
    private $eventManager;
    
    /**
     * @param Config $config
     * @param Logger $logger
     * @param EventManager $eventManager
     */
    public function __construct(
        Config $config,
        Logger $logger,
        EventManager $eventManager
    ) {
        $this->config = $config;
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
            $product = $observer->getEvent()->getProduct();
            
            // Dispatch custom event for AI agents to process
            $this->eventManager->dispatch(
                'ai_inventory_product_saved',
                ['product' => $product]
            );
            
            $this->logger->info('Processed product save event for SKU: ' . $product->getSku());
        } catch (\Exception $e) {
            $this->logger->error('Error processing product saved event: ' . $e->getMessage());
        }
    }
} 