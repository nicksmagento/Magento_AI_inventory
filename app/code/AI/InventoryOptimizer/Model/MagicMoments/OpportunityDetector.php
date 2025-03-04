<?php
namespace AI\InventoryOptimizer\Model\MagicMoments;

use AI\InventoryOptimizer\Api\Data\MagicMomentInterface;
use AI\InventoryOptimizer\Api\MagicMomentRepositoryInterface;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Model\ResourceModel\Product\Collection as ProductCollection;
use AI\InventoryOptimizer\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\ClientInterface;

class OpportunityDetector implements \AI\InventoryOptimizer\Api\OpportunityDetectionInterface
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Curl
     */
    private $curl;
    
    /**
     * @var Json
     */
    private $json;
    
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
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var ClientInterface
     */
    private $httpClient;
    
    /**
     * @param Config $config
     * @param Curl $curl
     * @param Json $json
     * @param Logger $logger
     * @param MagicMomentRepositoryInterface $magicMomentRepository
     * @param MagicMomentInterfaceFactory $magicMomentFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param ClientInterface $httpClient
     */
    public function __construct(
        Config $config,
        Curl $curl,
        Json $json,
        Logger $logger,
        MagicMomentRepositoryInterface $magicMomentRepository,
        \AI\InventoryOptimizer\Api\Data\MagicMomentInterfaceFactory $magicMomentFactory,
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        ClientInterface $httpClient
    ) {
        $this->config = $config;
        $this->curl = $curl;
        $this->json = $json;
        $this->logger = $logger;
        $this->magicMomentRepository = $magicMomentRepository;
        $this->magicMomentFactory = $magicMomentFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->httpClient = $httpClient;
    }
    
    /**
     * Detect all opportunities
     *
     * @return void
     */
    public function detectAllOpportunities()
    {
        if (!$this->config->isMagicMomentsEnabled()) {
            return;
        }
        
        $this->logger->info('Starting opportunity detection');
        
        try {
            if ($this->config->isCompetitorStockoutDetectionEnabled()) {
                $this->detectCompetitorStockouts();
            }
            
            if ($this->config->isSocialTrendDetectionEnabled()) {
                $this->detectSocialTrends();
            }
            
            if ($this->config->isWeatherIssueDetectionEnabled()) {
                $this->detectWeatherIssues();
            }
            
            $this->generateSuccessStories();
            
            $this->logger->info('Opportunity detection completed');
        } catch (\Exception $e) {
            $this->logger->error('Error during opportunity detection: ' . $e->getMessage());
        }
    }
    
    /**
     * Detect competitor stockouts
     *
     * @return void
     */
    public function detectCompetitorStockouts()
    {
        $this->logger->info('Detecting competitor stockouts');
        
        try {
            $apiKey = $this->config->getCompetitorApiKey();
            if (empty($apiKey)) {
                $this->logger->warning('Competitor API key not configured');
                return;
            }
            
            // Get top selling products
            $productCollection = $this->getTopSellingProducts(20);
            $skus = [];
            
            foreach ($productCollection as $product) {
                $skus[] = $product->getSku();
            }
            
            if (empty($skus)) {
                return;
            }
            
            // Call competitor API (simulated here)
            $competitorData = $this->getCompetitorData($skus);
            
            // Process results
            foreach ($competitorData as $sku => $data) {
                if (isset($data['in_stock']) && $data['in_stock'] === false) {
                    // Competitor is out of stock for this product
                    $product = $productCollection->getItemByColumnValue('sku', $sku);
                    if (!$product) {
                        continue;
                    }
                    
                    $competitorName = $data['competitor_name'] ?? 'A major competitor';
                    $estimatedDemand = $data['estimated_demand'] ?? 0;
                    $potentialRevenue = $product->getPrice() * $estimatedDemand;
                    
                    // Create magic moment
                    $moment = $this->magicMomentFactory->create();
                    $moment->setMomentType('competitor_stockout');
                    $moment->setTitle('Competitor Stockout Opportunity');
                    $moment->setDescription(
                        sprintf(
                            '%s is out of stock for "%s" (%s). Based on their typical sales volume, ' .
                            'this could represent an opportunity for approximately %d additional sales ' .
                            'worth $%.2f if you promote this product.',
                            $competitorName,
                            $product->getName(),
                            $sku,
                            $estimatedDemand,
                            $potentialRevenue
                        )
                    );
                    $moment->setImpactValue($potentialRevenue);
                    $moment->setImpactType('revenue');
                    $moment->setData($this->json->serialize([
                        'sku' => $sku,
                        'product_id' => $product->getId(),
                        'competitor' => $data['competitor_name'] ?? '',
                        'estimated_demand' => $estimatedDemand,
                        'suggested_action' => 'Consider running a targeted promotion for this product'
                    ]));
                    
                    $this->magicMomentRepository->save($moment);
                    
                    $this->logger->info(
                        sprintf(
                            'Created competitor stockout magic moment for SKU %s with potential revenue $%.2f',
                            $sku,
                            $potentialRevenue
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error detecting competitor stockouts: ' . $e->getMessage());
        }
    }
    
    /**
     * Detect social media trends
     *
     * @return void
     */
    public function detectSocialTrends()
    {
        $this->logger->info('Detecting social media trends');
        
        try {
            $apiKey = $this->config->getSocialTrendsApiKey();
            if (empty($apiKey)) {
                $this->logger->warning('Social trends API key not configured');
                return;
            }
            
            // Get product categories
            $categories = $this->getProductCategories();
            
            // Call social trends API (simulated here)
            $trendsData = $this->getSocialTrendsData($categories);
            
            foreach ($trendsData as $trend) {
                // Find matching products
                $matchingProducts = $this->findProductsMatchingTrend($trend);
                
                if (count($matchingProducts) > 0) {
                    $productNames = array_map(function ($product) {
                        return $product->getName();
                    }, $matchingProducts);
                    
                    $skus = array_map(function ($product) {
                        return $product->getSku();
                    }, $matchingProducts);
                    
                    $platform = $trend['platform'] ?? 'social media';
                    $trendName = $trend['trend_name'] ?? 'trending topic';
                    $growthRate = $trend['growth_rate'] ?? 0;
                    $potentialRevenue = $trend['potential_revenue'] ?? 0;
                    
                    // Create magic moment
                    $moment = $this->magicMomentFactory->create();
                    $moment->setMomentType('social_trend');
                    $moment->setTitle('Social Media Trend Opportunity');
                    $moment->setDescription(
                        sprintf(
                            'Products like yours are trending on %s! The "%s" trend is growing at %d%% ' .
                            'and your products (%s) match this trend. Consider increasing inventory and ' .
                            'creating social media content to capitalize on this trend.',
                            $platform,
                            $trendName,
                            $growthRate * 100,
                            implode(', ', array_slice($productNames, 0, 3)) . 
                                (count($productNames) > 3 ? ' and others' : '')
                        )
                    );
                    $moment->setImpactValue($potentialRevenue);
                    $moment->setImpactType('revenue');
                    $moment->setData($this->json->serialize([
                        'trend' => $trendName,
                        'platform' => $platform,
                        'growth_rate' => $growthRate,
                        'matching_skus' => $skus,
                        'suggested_action' => 'Increase inventory and create targeted social media content'
                    ]));
                    
                    $this->magicMomentRepository->save($moment);
                    
                    $this->logger->info(
                        sprintf(
                            'Created social trend magic moment for trend "%s" with %d matching products',
                            $trendName,
                            count($matchingProducts)
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error detecting social trends: ' . $e->getMessage());
        }
    }
    
    /**
     * Detect weather-related shipping issues
     *
     * @return void
     */
    public function detectWeatherIssues()
    {
        $this->logger->info('Detecting weather-related shipping issues');
        
        try {
            $apiKey = $this->config->getWeatherApiKey();
            if (empty($apiKey)) {
                $this->logger->warning('Weather API key not configured');
                return;
            }
            
            // Get warehouse locations
            $warehouses = $this->getWarehouses();
            
            // Call weather API for each warehouse location (simulated here)
            $weatherAlerts = [];
            foreach ($warehouses as $warehouse) {
                $location = $warehouse['location'] ?? '';
                if (empty($location)) {
                    continue;
                }
                
                $weatherData = $this->getWeatherData($location);
                
                if (isset($weatherData['alerts']) && !empty($weatherData['alerts'])) {
                    $weatherAlerts[$warehouse['code']] = [
                        'warehouse' => $warehouse,
                        'alerts' => $weatherData['alerts']
                    ];
                }
            }
            
            // Process weather alerts
            foreach ($weatherAlerts as $warehouseCode => $data) {
                $warehouse = $data['warehouse'];
                $alerts = $data['alerts'];
                
                foreach ($alerts as $alert) {
                    $alertType = $alert['type'] ?? 'weather event';
                    $severity = $alert['severity'] ?? 'moderate';
                    $startDate = $alert['start_date'] ?? $this->dateTime->gmtDate();
                    $endDate = $alert['end_date'] ?? $this->dateTime->gmtDate('+2 days');
                    $affectedOrders = $alert['affected_orders'] ?? 0;
                    
                    // Create magic moment
                    $moment = $this->magicMomentFactory->create();
                    $moment->setMomentType('weather_alert');
                    $moment->setTitle('Weather Alert Affecting Shipping');
                    $moment->setDescription(
                        sprintf(
                            'A %s %s is predicted for the %s area from %s to %s. ' .
                            'This could affect approximately %d orders shipping from %s. ' .
                            'Consider routing orders through alternative warehouses during this period.',
                            $severity,
                            $alertType,
                            $warehouse['location'],
                            date('M j', strtotime($startDate)),
                            date('M j', strtotime($endDate)),
                            $affectedOrders,
                            $warehouse['name']
                        )
                    );
                    $moment->setImpactValue($affectedOrders);
                    $moment->setImpactType('orders');
                    $moment->setData($this->json->serialize([
                        'warehouse_code' => $warehouseCode,
                        'alert_type' => $alertType,
                        'severity' => $severity,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'affected_orders' => $affectedOrders,
                        'suggested_action' => 'Temporarily route orders through alternative warehouses'
                    ]));
                    
                    $this->magicMomentRepository->save($moment);
                    
                    $this->logger->info(
                        sprintf(
                            'Created weather alert magic moment for warehouse %s with %d potentially affected orders',
                            $warehouse['name'],
                            $affectedOrders
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error detecting weather issues: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate success stories
     *
     * @return void
     */
    public function generateSuccessStories()
    {
        $this->logger->info('Generating success stories');
        
        try {
            // Get recent successful recommendations
            $successfulRecommendations = $this->getSuccessfulRecommendations();
            
            if (empty($successfulRecommendations)) {
                return;
            }
            
            // Group by type
            $groupedRecommendations = [];
            foreach ($successfulRecommendations as $recommendation) {
                $type = $recommendation['type'] ?? 'other';
                if (!isset($groupedRecommendations[$type])) {
                    $groupedRecommendations[$type] = [];
                }
                $groupedRecommendations[$type][] = $recommendation;
            }
            
            // Generate a success story for each type
            foreach ($groupedRecommendations as $type => $recommendations) {
                $count = count($recommendations);
                $totalImpact = 0;
                $impactType = '';
                
                foreach ($recommendations as $recommendation) {
                    $totalImpact += $recommendation['impact_value'] ?? 0;
                    $impactType = $recommendation['impact_type'] ?? '';
                }
                
                $title = '';
                $description = '';
                
                switch ($type) {
                    case 'reorder':
                        $title = 'Reorder Recommendations Success';
                        $description = sprintf(
                            'In the past 30 days, you\'ve implemented %d reorder recommendations from the AI, ' .
                            'resulting in approximately $%.2f in additional revenue. ' .
                            'This has helped prevent potential stockouts and maintain optimal inventory levels.',
                            $count,
                            $totalImpact
                        );
                        break;
                        
                    case 'stock_transfer':
                        $title = 'Stock Transfer Success';
                        $description = sprintf(
                            'In the past 30 days, you\'ve completed %d AI-recommended stock transfers, ' .
                            'optimizing your inventory distribution across warehouses. ' .
                            'This has improved delivery times by an average of %.1f days ' .
                            'and potentially prevented $%.2f in lost sales.',
                            $count,
                            $totalImpact / $count,
                            $totalImpact
                        );
                        break;
                        
                    case 'fraud_detection':
                        $title = 'Fraud Prevention Success';
                        $description = sprintf(
                            'In the past 30 days, the AI has helped identify %d potentially fraudulent orders, ' .
                            'saving approximately $%.2f in potential losses. ' .
                            'The system continues to learn from these cases to improve future detection.',
                            $count,
                            $totalImpact
                        );
                        break;
                        
                    default:
                        $title = 'AI Recommendation Success';
                        $description = sprintf(
                            'In the past 30 days, you\'ve implemented %d AI recommendations, ' .
                            'resulting in approximately $%.2f in business impact. ' .
                            'The AI system continues to learn from your business patterns to provide better insights.',
                            $count,
                            $totalImpact
                        );
                }
                
                // Create magic moment
                $moment = $this->magicMomentFactory->create();
                $moment->setMomentType('success_story');
                $moment->setTitle($title);
                $moment->setDescription($description);
                $moment->setImpactValue($totalImpact);
                $moment->setImpactType($impactType);
                $moment->setData($this->json->serialize([
                    'recommendation_type' => $type,
                    'count' => $count,
                    'total_impact' => $totalImpact,
                    'impact_type' => $impactType,
                    'period' => '30 days'
                ]));
                
                $this->magicMomentRepository->save($moment);
                
                $this->logger->info(
                    sprintf(
                        'Created success story magic moment for %s with impact value %.2f',
                        $type,
                        $totalImpact
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Error generating success stories: ' . $e->getMessage());
        }
    }
    
    /**
     * Get top selling products
     *
     * @param int $limit
     * @return ProductCollection
     */
    private function getTopSellingProducts($limit = 10)
    {
        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku', 'price']);
        $collection->setOrder('sold_quantity', 'DESC');
        $collection->setPageSize($limit);
        
        return $collection;
    }
    
    /**
     * Get competitor data (simulated)
     *
     * @param array $skus
     * @return array
     */
    private function getCompetitorData($skus)
    {
        // In a real implementation, this would call an external API
        // For simulation, we'll return random data
        $result = [];
        $competitors = ['CompetitorA', 'CompetitorB', 'CompetitorC'];
        
        foreach ($skus as $sku) {
            // Randomly determine if competitor is out of stock (20% chance)
            $isOutOfStock = (mt_rand(1, 100) <= 20);
            
            if ($isOutOfStock) {
                $result[$sku] = [
                    'in_stock' => false,
                    'competitor_name' => $competitors[array_rand($competitors)],
                    'estimated_demand' => mt_rand(10, 100),
                    'last_checked' => $this->dateTime->gmtDate()
                ];
            } else {
                $result[$sku] = [
                    'in_stock' => true,
                    'competitor_name' => $competitors[array_rand($competitors)],
                    'last_checked' => $this->dateTime->gmtDate()
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Get product categories
     *
     * @return array
     */
    private function getProductCategories()
    {
        // In a real implementation, this would query the database
        // For simulation, we'll return sample categories
        return [
            'clothing',
            'electronics',
            'home',
            'beauty',
            'sports'
        ];
    }
    
    /**
     * Get social trends data (simulated)
     *
     * @param array $categories
     * @return array
     */
    private function getSocialTrendsData($categories)
    {
        // In a real implementation, this would call an external API
        // For simulation, we'll return random data
        $result = [];
        $platforms = ['TikTok', 'Instagram', 'Pinterest', 'Twitter'];
        $trendPrefixes = ['Summer', 'Eco-friendly', 'Minimalist', 'Vintage', 'Smart'];
        $trendSuffixes = ['Style', 'Living', 'Essentials', 'Collection', 'Tech'];
        
        // Generate 1-3 random trends
        $trendCount = mt_rand(1, 3);
        
        for ($i = 0; $i < $trendCount; $i++) {
            $category = $categories[array_rand($categories)];
            $platform = $platforms[array_rand($platforms)];
            $trendName = $trendPrefixes[array_rand($trendPrefixes)] . ' ' . 
                         $trendSuffixes[array_rand($trendSuffixes)];
            
            $result[] = [
                'trend_name' => $trendName,
                'platform' => $platform,
                'category' => $category,
                'growth_rate' => mt_rand(15, 50) / 100,
                'potential_revenue' => mt_rand(500, 5000),
                'start_date' => $this->dateTime->gmtDate('-' . mt_rand(1, 7) . ' days')
            ];
        }
        
        return $result;
    }
    
    /**
     * Find products matching a trend (simulated)
     *
     * @param array $trend
     * @return array
     */
    private function findProductsMatchingTrend($trend)
    {
        // In a real implementation, this would query the database
        // For simulation, we'll create sample product objects
        $result = [];
        $category = $trend['category'] ?? '';
        
        if (empty($category)) {
            return $result;
        }
        
        // Create 2-5 sample products
        $productCount = mt_rand(2, 5);
        
        for ($i = 0; $i < $productCount; $i++) {
            $product = new \Magento\Framework\DataObject();
            $product->setId(1000 + $i);
            $product->setSku('TREND-' . $category . '-' . $i);
            $product->setName(ucfirst($category) . ' Product ' . ($i + 1));
            $product->setPrice(mt_rand(20, 200));
            
            $result[] = $product;
        }
        
        return $result;
    }
    
    /**
     * Get warehouses
     *
     * @return array
     */
    private function getWarehouses()
    {
        // In a real implementation, this would query the database
        // For simulation, we'll return sample warehouses
        return [
            [
                'code' => 'whse_a',
                'name' => 'East Coast Warehouse',
                'location' => 'New York, NY'
            ],
            [
                'code' => 'whse_b',
                'name' => 'West Coast Warehouse',
                'location' => 'Los Angeles, CA'
            ],
            [
                'code' => 'whse_c',
                'name' => 'Central Warehouse',
                'location' => 'Chicago, IL'
            ],
            [
                'code' => 'whse_d',
                'name' => 'Southern Warehouse',
                'location' => 'Dallas, TX'
            ]
        ];
    }
    
    /**
     * Get weather data (simulated)
     *
     * @param string $location
     * @return array
     */
    private function getWeatherData($location)
    {
        // In a real implementation, this would call an external API
        // For simulation, we'll return random data
        $result = [
            'location' => $location,
            'current' => [
                'temp' => mt_rand(50, 90),
                'condition' => 'clear'
            ],
            'alerts' => []
        ];
        
        // 30% chance of a weather alert
        if (mt_rand(1, 100) <= 30) {
            $alertTypes = ['snow storm', 'heavy rain', 'flooding', 'high winds', 'extreme heat'];
            $severities = ['minor', 'moderate', 'severe', 'extreme'];
            
            $alertType = $alertTypes[array_rand($alertTypes)];
            $severity = $severities[array_rand($severities)];
            
            $startDate = $this->dateTime->gmtDate('+' . mt_rand(1, 3) . ' days');
            $endDate = date('Y-m-d H:i:s', strtotime($startDate) + (mt_rand(24, 72) * 3600));
            
            $result['alerts'][] = [
                'type' => $alertType,
                'severity' => $severity,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'affected_orders' => mt_rand(5, 50)
            ];
        }
        
        return $result;
    }
    
    /**
     * Get successful recommendations (simulated)
     *
     * @return array
     */
    private function getSuccessfulRecommendations()
    {
        // In a real implementation, this would query the database
        // For simulation, we'll return sample data
        $result = [];
        
        // Generate reorder recommendations
        $reorderCount = mt_rand(3, 10);
        for ($i = 0; $i < $reorderCount; $i++) {
            $result[] = [
                'type' => 'reorder',
                'impact_value' => mt_rand(500, 5000),
                'impact_type' => 'revenue',
                'date_implemented' => $this->dateTime->gmtDate('-' . mt_rand(1, 30) . ' days')
            ];
        }
        
        // Generate stock transfer recommendations
        $transferCount = mt_rand(2, 8);
        for ($i = 0; $i < $transferCount; $i++) {
            $result[] = [
                'type' => 'stock_transfer',
                'impact_value' => mt_rand(1, 5),
                'impact_type' => 'days',
                'date_implemented' => $this->dateTime->gmtDate('-' . mt_rand(1, 30) . ' days')
            ];
        }
        
        // Generate fraud detection recommendations
        $fraudCount = mt_rand(1, 5);
        for ($i = 0; $i < $fraudCount; $i++) {
            $result[] = [
                'type' => 'fraud_detection',
                'impact_value' => mt_rand(100, 2000),
                'impact_type' => 'savings',
                'date_implemented' => $this->dateTime->gmtDate('-' . mt_rand(1, 30) . ' days')
            ];
        }
        
        return $result;
    }
} 