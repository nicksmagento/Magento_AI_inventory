<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\OrderRouterAgent;
use Psr\Log\LoggerInterface;

class NewOrderObserver implements ObserverInterface
{
    /**
     * @var OrderRouterAgent
     */
    private $orderRouterAgent;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param OrderRouterAgent $orderRouterAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRouterAgent $orderRouterAgent,
        LoggerInterface $logger
    ) {
        $this->orderRouterAgent = $orderRouterAgent;
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
            $order = $observer->getEvent()->getOrder();
            
            if (!$order) {
                return;
            }
            
            // Route the order to the optimal fulfillment center
            $this->orderRouterAgent->routeOrder($order->getId());
            
            $this->logger->info(sprintf(
                'AI Order Router: Processing new order %s',
                $order->getIncrementId()
            ));
        } catch (\Exception $e) {
            $this->logger->error('AI Order Router Observer Error: ' . $e->getMessage());
        }
    }
} 