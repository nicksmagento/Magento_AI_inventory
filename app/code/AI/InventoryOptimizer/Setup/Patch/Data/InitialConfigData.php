<?php
namespace AI\InventoryOptimizer\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class InitialConfigData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * @var WriterInterface
     */
    private $configWriter;
    
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
    }
    
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        
        // Set default configuration values
        $defaultConfig = [
            'ai_inventory/general/enabled' => 1,
            'ai_inventory/forecasting/enabled' => 1,
            'ai_inventory/forecasting/forecast_horizon' => 30,
            'ai_inventory/forecasting/confidence_threshold' => 0.75,
            'ai_inventory/stock_balancer/enabled' => 1,
            'ai_inventory/stock_balancer/auto_transfer' => 0,
            'ai_inventory/order_routing/enabled' => 1,
            'ai_inventory/fraud_detection/enabled' => 1,
            'ai_inventory/fraud_detection/auto_hold' => 1,
            'ai_inventory/fraud_detection/risk_threshold' => 0.8,
            'ai_inventory/chat_copilot/enabled' => 1
        ];
        
        foreach ($defaultConfig as $path => $value) {
            $this->configWriter->save($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        }
        
        $this->moduleDataSetup->endSetup();
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
} 