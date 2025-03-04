<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Model\StockBalancerAgent;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class StockBalancing
{
    /**
     * @var StockBalancerAgent
     */
    private $stockBalancerAgent;
    
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param StockBalancerAgent $stockBalancerAgent
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockBalancerAgent $stockBalancerAgent,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->stockBalancerAgent = $stockBalancerAgent;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }
    
    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->logger->info('AI Stock Balancing: Starting warehouse balancing run');
            
            // Get all active products
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('status', 1) // Only active products
                ->create();
            
            $products = $this->productRepository->getList($searchCriteria);
            
            $processedCount = 0;
            foreach ($products->getItems() as $product) {
                // Balance stock for each product
                $this->stockBalancerAgent->balanceStockForSku($product->getSku());
                $processedCount++;
            }
            
            $this->logger->info(sprintf(
                'AI Stock Balancing: Completed warehouse balancing for %d products',
                $processedCount
            ));
        } catch (\Exception $e) {
            $this->logger->error('AI Stock Balancing Cron Error: ' . $e->getMessage());
        }
    }
} 