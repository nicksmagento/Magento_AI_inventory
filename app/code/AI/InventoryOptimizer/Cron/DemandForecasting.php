<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Model\ReorderAgent;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class DemandForecasting
{
    /**
     * @var ReorderAgent
     */
    private $reorderAgent;
    
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
     * @param ReorderAgent $reorderAgent
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReorderAgent $reorderAgent,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->reorderAgent = $reorderAgent;
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
            $this->logger->info('AI Demand Forecasting: Starting daily forecast run');
            
            // Get all active products
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('status', 1) // Only active products
                ->create();
            
            $products = $this->productRepository->getList($searchCriteria);
            
            $processedCount = 0;
            foreach ($products->getItems() as $product) {
                // Process reorder for each product
                $this->reorderAgent->processReorderForSku($product->getSku());
                $processedCount++;
            }
            
            $this->logger->info(sprintf(
                'AI Demand Forecasting: Completed daily forecast run for %d products',
                $processedCount
            ));
        } catch (\Exception $e) {
            $this->logger->error('AI Demand Forecasting Cron Error: ' . $e->getMessage());
        }
    }
} 