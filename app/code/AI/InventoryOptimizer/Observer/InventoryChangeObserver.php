<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\ReorderAgent;
use AI\InventoryOptimizer\Model\StockBalancerAgent;
use Psr\Log\LoggerInterface;

class InventoryChangeObserver implements ObserverInterface
{
    /**
     * @var ReorderAgent
     */
    private $reorderAgent;
    
    /**
     * @var StockBalancerAgent
     */
    private $stockBalancerAgent;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param ReorderAgent $reorderAgent
     * @param StockBalancerAgent $stockBalancerAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReorderAgent $reorderAgent,
        StockBalancerAgent $stockBalancerAgent,
        LoggerInterface $logger
    ) {
        $this->reorderAgent = $reorderAgent;
        $this->stockBalancerAgent = $stockBalancerAgent;
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
            $stockItem = $observer->getEvent()->getItem();
            
            if (!$stockItem) {
                return;
            }
            
            $sku = $stockItem->getProductId();
            
            // Check if stock is low and trigger reorder agent
            if ($stockItem->getQty() <= $stockItem->getMinQty()) {
                $this->reorderAgent->processReorderForSku($sku);
            }
            
            // Trigger stock balancer to optimize inventory across warehouses
            $this->stockBalancerAgent->balanceStockForSku($sku);
            
            $this->logger->info(sprintf(
                'AI Inventory: Processed inventory change for SKU %s, current qty: %s',
                $sku,
                $stockItem->getQty()
            ));
        } catch (\Exception $e) {
            $this->logger->error('AI Inventory Observer Error: ' . $e->getMessage());
        }
    }
} 