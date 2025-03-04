<?php
namespace AI\InventoryOptimizer\Block\Adminhtml\Integration;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use AI\InventoryOptimizer\Model\IntegrationRegistry;
use AI\InventoryOptimizer\Model\Config;

class Grid extends Template
{
    /**
     * @var string
     */
    protected $_template = 'AI_InventoryOptimizer::integration/grid.phtml';
    
    /**
     * @var IntegrationRegistry
     */
    private $integrationRegistry;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @param Context $context
     * @param IntegrationRegistry $integrationRegistry
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        IntegrationRegistry $integrationRegistry,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->integrationRegistry = $integrationRegistry;
        $this->config = $config;
    }
    
    /**
     * Get all integrations grouped by type
     *
     * @return array
     */
    public function getIntegrationsByType()
    {
        $result = [];
        $availableIntegrations = $this->integrationRegistry->getAvailableIntegrations();
        
        foreach ($availableIntegrations as $code => $class) {
            $integration = $this->integrationRegistry->getIntegration($code);
            if (!$integration) {
                continue;
            }
            
            $type = $this->getIntegrationType($code);
            
            if (!isset($result[$type])) {
                $result[$type] = [
                    'label' => $this->getTypeLabel($type),
                    'integrations' => []
                ];
            }
            
            $result[$type]['integrations'][$code] = [
                'code' => $code,
                'name' => $integration->getName(),
                'enabled' => $integration->isEnabled(),
                'status' => $this->getIntegrationStatus($integration)
            ];
        }
        
        return $result;
    }
    
    /**
     * Get integration type from code
     *
     * @param string $code
     * @return string
     */
    private function getIntegrationType($code)
    {
        $typeMap = [
            'sap' => 'erp',
            'netsuite' => 'erp',
            'dynamics' => 'erp',
            'brightpearl' => 'ims',
            'cin7' => 'ims',
            'tradegecko' => 'ims',
            'zoho' => 'ims',
            'fishbowl' => 'ims',
            'katana' => 'ims',
            'orderbot' => 'oms',
            'shipstation' => 'oms',
            'linnworks' => 'oms',
            'orderhive' => 'oms',
            'skubana' => 'oms',
            'manhattan' => 'wms',
            'highjump' => 'wms',
            'logiwa' => 'wms',
            'shiphero' => 'wms',
            'threepl' => 'wms',
            'amazon' => 'marketplace',
            'walmart' => 'marketplace',
            'ebay' => 'marketplace',
            'etsy' => 'marketplace',
            'target' => 'marketplace'
        ];
        
        return $typeMap[$code] ?? 'other';
    }
    
    /**
     * Get type label
     *
     * @param string $type
     * @return string
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'erp' => __('ERP Systems'),
            'ims' => __('Inventory Management Systems'),
            'oms' => __('Order Management Systems'),
            'wms' => __('Warehouse Management Systems'),
            'marketplace' => __('Marketplace Integrations'),
            'other' => __('Other Integrations')
        ];
        
        return $labels[$type] ?? __('Other Integrations');
    }
    
    /**
     * Get integration status
     *
     * @param \AI\InventoryOptimizer\Api\IntegrationInterface $integration
     * @return array
     */
    private function getIntegrationStatus($integration)
    {
        if (!$integration->isEnabled()) {
            return [
                'state' => 'disabled',
                'label' => __('Disabled'),
                'class' => 'grid-severity-minor'
            ];
        }
        
        try {
            $status = $integration->getStatus();
            
            if (isset($status['connected']) && $status['connected']) {
                return [
                    'state' => 'connected',
                    'label' => __('Connected'),
                    'class' => 'grid-severity-notice'
                ];
            } else {
                return [
                    'state' => 'error',
                    'label' => isset($status['error']) ? __('Error: %1', $status['error']) : __('Not Connected'),
                    'class' => 'grid-severity-critical'
                ];
            }
        } catch (\Exception $e) {
            return [
                'state' => 'error',
                'label' => __('Error: %1', $e->getMessage()),
                'class' => 'grid-severity-critical'
            ];
        }
    }
    
    /**
     * Get sync URL
     *
     * @param string $code
     * @return string
     */
    public function getSyncUrl($code)
    {
        return $this->getUrl('ai_inventory/integration/sync', ['code' => $code]);
    }
    
    /**
     * Get config URL
     *
     * @param string $code
     * @return string
     */
    public function getConfigUrl($code)
    {
        $type = $this->getIntegrationType($code);
        return $this->getUrl('adminhtml/system_config/edit', [
            'section' => 'ai_inventory',
            '_fragment' => 'ai_inventory_integrations_' . $type . '_' . $code . '-link'
        ]);
    }
} 