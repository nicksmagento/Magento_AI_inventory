<?php
namespace AI\InventoryOptimizer\Model\Onboarding;

use AI\InventoryOptimizer\Api\Data\MagicMomentInterface;
use AI\InventoryOptimizer\Api\MagicMomentRepositoryInterface;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class InstantValueGenerator
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
     * @var MagicMomentRepositoryInterface
     */
    private $magicMomentRepository;
    
    /**
     * @var MagicMomentInterfaceFactory
     */
    private $magicMomentFactory;
    
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;
    
    /**
     * @var Json
     */
    private $json;
    
    /**
     * @param Config $config
     * @param Logger $logger
     * @param MagicMomentRepositoryInterface $magicMomentRepository
     * @param \AI\InventoryOptimizer\Api\Data\MagicMomentInterfaceFactory $magicMomentFactory
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param Json $json
     */
    public function __construct(
        Config $config,
        Logger $logger,
        MagicMomentRepositoryInterface $magicMomentRepository,
        \AI\InventoryOptimizer\Api\Data\MagicMomentInterfaceFactory $magicMomentFactory,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        Json $json
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->magicMomentRepository = $magicMomentRepository;
        $this->magicMomentFactory = $magicMomentFactory;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->json = $json;
    }
    
    /**
     * Generate instant value insights
     *
     * @return void
     */
    public function generateInstantInsights()
    {
        $this->logger->info('Generating instant insights for onboarding');
        
        try {
            $this->identifyPotentialStockouts();
            $this->identifyInventoryImbalances();
            $this->identifyLowSafetyStock();
            $this->identifySeasonalProducts();
            
            $this->logger->info('Instant insights generation completed');
        } catch (\Exception $e) {
            $this->logger->error('Error generating instant insights: ' . $e->getMessage());
        }
    }
    
    /**
     * Identify potential stockouts
     *
     * @return void
     */
    private function identifyPotentialStockouts()
    {
        $this->logger->info('Identifying potential stockouts');
        
        try {
            // Get products with low stock
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('quantity_and_stock_status', 1, 'eq')
                ->create();
            
            $products = $this->productRepository->getList($searchCriteria)->getItems();
            
            $lowStockProducts = [];
            foreach ($products as $product) {
                $sku = $product->getSku();
                $sourceItems = $this->getSourceItemsBySku->execute($sku);
                
                $totalQty = 0;
                foreach ($sourceItems as $sourceItem) {
                    $totalQty += $sourceItem->getQuantity();
                }
                
                // Check if product has low stock
                if ($totalQty <= 5) {
                    $lowStockProducts[] = [
                        'product' => $product,
                        'qty' => $totalQty
                    ];
                }
                
                // Limit to 5 products for instant insights
                if (count($lowStockProducts) >= 5) {
                    break;
                }
            }
            
            if (!empty($lowStockProducts)) {
                // Create magic moment for low stock products
                $productNames = array_map(function ($item) {
                    return $item['product']->getName() . ' (' . $item['qty'] . ' left)';
                }, $lowStockProducts);
                
                $skus = array_map(function ($item) {
                    return $item['product']->getSku();
                }, $lowStockProducts);
                
                $moment = $this->magicMomentFactory->create();
                $moment->setMomentType('instant_insight');
                $moment->setTitle('Potential Stockout Alert');
                $moment->setDescription(
                    sprintf(
                        'We\'ve identified %d products that are at risk of stockout: %s. ' .
                        'Consider reordering these items soon to prevent lost sales.',
                        count($lowStockProducts),
                        implode(', ', $productNames)
                    )
                );
                $moment->setImpactValue(count($lowStockProducts));
                $moment->setImpactType('products');
                $moment->setData($this->json->serialize([
                    'skus' => $skus,
                    'suggested_action' => 'Review and reorder these products'
                ]));
                
                $this->magicMomentRepository->save($moment);
                
                $this->logger->info(
                    sprintf(
                        'Created instant insight for %d potential stockouts',
                        count($lowStockProducts)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Error identifying potential stockouts: ' . $e->getMessage());
        }
    }
    
    /**
     * Identify inventory imbalances
     *
     * @return void
     */
    private function identifyInventoryImbalances()
    {
        $this->logger->info('Identifying inventory imbalances');
        
        try {
            // This would normally query the database for inventory across warehouses
            // For simulation, we'll create sample data
            $imbalances = $this->getSimulatedInventoryImbalances();
            
            if (!empty($imbalances)) {
                $productNames = array_map(function ($item) {
                    return $item['product_name'] . ' (' . $item['source_qty'] . ' vs ' . $item['dest_qty'] . ')';
                }, $imbalances);
                
                $moment = $this->magicMomentFactory->create();
                $moment->setMomentType('instant_insight');
                $moment->setTitle('Inventory Imbalance Detected');
                $moment->setDescription(
                    sprintf(
                        'We\'ve detected inventory imbalances across your warehouses. ' .
                        'For example: %s. ' .
                        'Balancing your inventory could improve delivery times and reduce shipping costs.',
                        implode(', ', array_slice($productNames, 0, 3)) . 
                            (count($productNames) > 3 ? ' and others' : '')
                    )
                );
                $moment->setImpactValue(count($imbalances));
                $moment->setImpactType('transfers');
                $moment->setData($this->json->serialize([
                    'imbalances' => $imbalances,
                    'suggested_action' => 'Review and balance inventory across warehouses'
                ]));
                
                $this->magicMomentRepository->save($moment);
                
                $this->logger->info(
                    sprintf(
                        'Created instant insight for %d inventory imbalances',
                        count($imbalances)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Error identifying inventory imbalances: ' . $e->getMessage());
        }
    }
    
    /**
     * Identify products with low safety stock
     *
     * @return void
     */
    private function identifyLowSafetyStock()
    {
        $this->logger->info('Identifying products with low safety stock');
        
        try {
            // This would normally analyze order history and current stock levels
            // For simulation, we'll create sample data
            $lowSafetyStockProducts = $this->getSimulatedLowSafetyStockProducts();
            
            if (!empty($lowSafetyStockProducts)) {
                $productNames = array_map(function ($item) {
                    return $item['product_name'] . ' (safety stock: ' . $item['current_safety_stock'] . 
                           ', recommended: ' . $item['recommended_safety_stock'] . ')';
                }, $lowSafetyStockProducts);
                
                $moment = $this->magicMomentFactory->create();
                $moment->setMomentType('instant_insight');
                $moment->setTitle('Safety Stock Optimization');
                $moment->setDescription(
                    sprintf(
                        'Your safety stock levels could be optimized for %d products, including: %s. ' .
                        'Adjusting these levels could prevent stockouts while minimizing excess inventory costs.',
                        count($lowSafetyStockProducts),
                        implode(', ', array_slice($productNames, 0, 3)) . 
                            (count($productNames) > 3 ? ' and others' : '')
                    )
                );
                $moment->setImpactValue(count($lowSafetyStockProducts));
                $moment->setImpactType('products');
                $moment->setData($this->json->serialize([
                    'products' => $lowSafetyStockProducts,
                    'suggested_action' => 'Review and adjust safety stock levels'
                ]));
                
                $this->magicMomentRepository->save($moment);
                
                $this->logger->info(
                    sprintf(
                        'Created instant insight for %d products with low safety stock',
                        count($lowSafetyStockProducts)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Error identifying low safety stock: ' . $e->getMessage());
        }
    }
    
    /**
     * Identify seasonal products
     *
     * @return void
     */
    private function identifySeasonalProducts()
    {
        $this->logger->info('Identifying seasonal products');
        
        try {
            // This would normally analyze order history for seasonal patterns
            // For simulation, we'll create sample data
            $seasonalProducts = $this->getSimulatedSeasonalProducts();
            
            if (!empty($seasonalProducts)) {
                $productNames = array_map(function ($item) {
                    return $item['product_name'] . ' (' . $item['season'] . ')';
                }, $seasonalProducts);
                
                $upcomingSeasons = array_unique(array_map(function ($item) {
                    return $item['season'];
                }, array_filter($seasonalProducts, function ($item) {
                    return $item['is_upcoming'];
                })));
                
                $moment = $this->magicMomentFactory->create();
                $moment->setMomentType('instant_insight');
                $moment->setTitle('Seasonal Product Planning');
                $moment->setDescription(
                    sprintf(
                        'We\'ve identified %d seasonal products in your inventory, including: %s. ' .
                        'Planning inventory for %s could help maximize sales during peak seasons.',
                        count($seasonalProducts),
                        implode(', ', array_slice($productNames, 0, 3)) . 
                            (count($productNames) > 3 ? ' and others' : ''),
                        !empty($upcomingSeasons) ? 'upcoming ' . implode(', ', $upcomingSeasons) . ' season' .
                            (count($upcomingSeasons) > 1 ? 's' : '') : 'seasonal periods'
                    )
                );
                $moment->setImpactValue(count($seasonalProducts));
                $moment->setImpactType('products');
                $moment->setData($this->json->serialize([
                    'products' => $seasonalProducts,
                    'upcoming_seasons' => $upcomingSeasons,
                    'suggested_action' => 'Plan inventory for seasonal products'
                ]));
                
                $this->magicMomentRepository->save($moment);
                
                $this->logger->info(
                    sprintf(
                        'Created instant insight for %d seasonal products',
                        count($seasonalProducts)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Error identifying seasonal products: ' . $e->getMessage());
        }
    }
    
    /**
     * Get simulated inventory imbalances
     *
     * @return array
     */
    private function getSimulatedInventoryImbalances()
    {
        return [
            [
                'sku' => 'WS12-M-Blue',
                'product_name' => 'Blue T-Shirt',
                'source_warehouse' => 'East Coast Warehouse',
                'source_qty' => 50,
                'destination_warehouse' => 'West Coast Warehouse',
                'dest_qty' => 5,
                'recommended_transfer' => 20
            ],
            [
                'sku' => 'MJ03-L-Red',
                'product_name' => 'Red Jacket',
                'source_warehouse' => 'Central Warehouse',
                'source_qty' => 35,
                'destination_warehouse' => 'Southern Warehouse',
                'dest_qty' => 3,
                'recommended_transfer' => 15
            ],
            [
                'sku' => 'SP20-XL-Black',
                'product_name' => 'Black Pants',
                'source_warehouse' => 'West Coast Warehouse',
                'source_qty' => 40,
                'destination_warehouse' => 'East Coast Warehouse',
                'dest_qty' => 8,
                'recommended_transfer' => 15
            ]
        ];
    }
    
    /**
     * Get simulated products with low safety stock
     *
     * @return array
     */
    private function getSimulatedLowSafetyStockProducts()
    {
        return [
            [
                'sku' => 'WS12-M-Blue',
                'product_name' => 'Blue T-Shirt',
                'current_safety_stock' => 5,
                'recommended_safety_stock' => 15,
                'avg_daily_sales' => 2.5
            ],
            [
                'sku' => 'MJ03-L-Red',
                'product_name' => 'Red Jacket',
                'current_safety_stock' => 3,
                'recommended_safety_stock' => 10,
                'avg_daily_sales' => 1.8
            ],
            [
                'sku' => 'SP20-XL-Black',
                'product_name' => 'Black Pants',
                'current_safety_stock' => 8,
                'recommended_safety_stock' => 20,
                'avg_daily_sales' => 3.2
            ],
            [
                'sku' => 'SH08-9-Brown',
                'product_name' => 'Brown Shoes',
                'current_safety_stock' => 4,
                'recommended_safety_stock' => 12,
                'avg_daily_sales' => 2.0
            ]
        ];
    }
    
    /**
     * Get simulated seasonal products
     *
     * @return array
     */
    private function getSimulatedSeasonalProducts()
    {
        $currentMonth = (int)date('n');
        
        return [
            [
                'sku' => 'WS12-M-Blue',
                'product_name' => 'Blue T-Shirt',
                'season' => 'Summer',
                'peak_months' => [6, 7, 8],
                'is_upcoming' => ($currentMonth >= 3 && $currentMonth <= 5),
                'avg_peak_sales' => 150,
                'avg_off_peak_sales' => 30
            ],
            [
                'sku' => 'MJ03-L-Red',
                'product_name' => 'Red Jacket',
                'season' => 'Winter',
                'peak_months' => [12, 1, 2],
                'is_upcoming' => ($currentMonth >= 9 && $currentMonth <= 11),
                'avg_peak_sales' => 120,
                'avg_off_peak_sales' => 15
            ],
            [
                'sku' => 'HT05-M-Green',
                'product_name' => 'Green Hat',
                'season' => 'Spring',
                'peak_months' => [3, 4, 5],
                'is_upcoming' => ($currentMonth == 12 || $currentMonth <= 2),
                'avg_peak_sales' => 80,
                'avg_off_peak_sales' => 20
            ],
            [
                'sku' => 'SC10-L-Orange',
                'product_name' => 'Orange Scarf',
                'season' => 'Fall',
                'peak_months' => [9, 10, 11],
                'is_upcoming' => ($currentMonth >= 6 && $currentMonth <= 8),
                'avg_peak_sales' => 90,
                'avg_off_peak_sales' => 10
            ]
        ];
    }
} 