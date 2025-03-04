<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\IntegrationInterface;
use Magento\Framework\ObjectManagerInterface;

class IntegrationRegistry
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var array
     */
    private $integrations = [];
    
    /**
     * @var array
     */
    private $integrationTypes = [
        'sap' => \AI\InventoryOptimizer\Model\Integration\Sap::class,
        'netsuite' => \AI\InventoryOptimizer\Model\Integration\NetSuite::class,
        'dynamics' => \AI\InventoryOptimizer\Model\Integration\Dynamics::class,
        'brightpearl' => \AI\InventoryOptimizer\Model\Integration\Brightpearl::class,
        'cin7' => \AI\InventoryOptimizer\Model\Integration\Cin7::class,
        'tradegecko' => \AI\InventoryOptimizer\Model\Integration\TradeGecko::class,
        'zoho' => \AI\InventoryOptimizer\Model\Integration\Zoho::class,
        'fishbowl' => \AI\InventoryOptimizer\Model\Integration\Fishbowl::class,
        'katana' => \AI\InventoryOptimizer\Model\Integration\Katana::class,
        'orderbot' => \AI\InventoryOptimizer\Model\Integration\Orderbot::class,
        'shipstation' => \AI\InventoryOptimizer\Model\Integration\ShipStation::class,
        'linnworks' => \AI\InventoryOptimizer\Model\Integration\Linnworks::class,
        'orderhive' => \AI\InventoryOptimizer\Model\Integration\OrderHive::class,
        'skubana' => \AI\InventoryOptimizer\Model\Integration\Skubana::class,
        'manhattan' => \AI\InventoryOptimizer\Model\Integration\Manhattan::class,
        'highjump' => \AI\InventoryOptimizer\Model\Integration\HighJump::class,
        'logiwa' => \AI\InventoryOptimizer\Model\Integration\Logiwa::class,
        'shiphero' => \AI\InventoryOptimizer\Model\Integration\ShipHero::class,
        'threepl' => \AI\InventoryOptimizer\Model\Integration\ThreePL::class,
        'amazon' => \AI\InventoryOptimizer\Model\Integration\Amazon::class,
        'walmart' => \AI\InventoryOptimizer\Model\Integration\Walmart::class,
        'ebay' => \AI\InventoryOptimizer\Model\Integration\Ebay::class,
        'etsy' => \AI\InventoryOptimizer\Model\Integration\Etsy::class,
        'target' => \AI\InventoryOptimizer\Model\Integration\Target::class
    ];
    
    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }
    
    /**
     * Get all available integrations
     *
     * @return array
     */
    public function getAvailableIntegrations()
    {
        return $this->integrationTypes;
    }
    
    /**
     * Get integration by code
     *
     * @param string $code
     * @return IntegrationInterface|null
     */
    public function getIntegration($code)
    {
        if (!isset($this->integrations[$code])) {
            if (!isset($this->integrationTypes[$code])) {
                return null;
            }
            
            $this->integrations[$code] = $this->objectManager->create($this->integrationTypes[$code]);
        }
        
        return $this->integrations[$code];
    }
    
    /**
     * Get all enabled integrations
     *
     * @return IntegrationInterface[]
     */
    public function getEnabledIntegrations()
    {
        $enabled = [];
        
        foreach (array_keys($this->integrationTypes) as $code) {
            $integration = $this->getIntegration($code);
            if ($integration && $integration->isEnabled()) {
                $enabled[$code] = $integration;
            }
        }
        
        return $enabled;
    }
    
    /**
     * Register custom integration
     *
     * @param string $code
     * @param string $class
     * @return void
     */
    public function registerIntegration($code, $class)
    {
        $this->integrationTypes[$code] = $class;
        
        // Clear cached instance if exists
        if (isset($this->integrations[$code])) {
            unset($this->integrations[$code]);
        }
    }
} 