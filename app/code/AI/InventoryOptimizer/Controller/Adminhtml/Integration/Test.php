<?php
namespace AI\InventoryOptimizer\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use AI\InventoryOptimizer\Model\IntegrationRegistry;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;

class Test extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var IntegrationRegistry
     */
    protected $integrationRegistry;
    
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param IntegrationRegistry $integrationRegistry
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        IntegrationRegistry $integrationRegistry,
        Config $config,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->integrationRegistry = $integrationRegistry;
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * Test integration connection
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $params = $this->getRequest()->getParams();
            $integrationCode = $params['integration_code'] ?? '';
            $integrationType = $params['integration_type'] ?? '';
            
            if (empty($integrationCode) || empty($integrationType)) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Missing integration code or type')
                ]);
            }
            
            // Temporarily store credentials for testing
            $this->storeTemporaryCredentials($integrationType, $integrationCode, $params);
            
            // Get integration instance
            $integration = $this->integrationRegistry->getIntegration($integrationCode);
            
            if (!$integration) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Integration "%1" not found', $integrationCode)
                ]);
            }
            
            // Test connection
            $connected = $integration->testConnection();
            
            if ($connected) {
                return $result->setData([
                    'success' => true,
                    'message' => __('Successfully connected to %1', $integration->getName())
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => __('Failed to connect to %1. Please check your credentials and try again.', $integration->getName())
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Integration test error: ' . $e->getMessage());
            
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Store temporary credentials for testing
     *
     * @param string $type
     * @param string $code
     * @param array $params
     * @return void
     */
    private function storeTemporaryCredentials($type, $code, array $params)
    {
        $prefix = 'groups[integrations][groups][' . $type . '][groups][' . $code . '][fields]';
        
        $apiUrl = $params[$prefix . '[api_url][value]'] ?? '';
        $clientId = $params[$prefix . '[client_id][value]'] ?? '';
        $clientSecret = $params[$prefix . '[client_secret][value]'] ?? '';
        
        if (!empty($apiUrl)) {
            $this->config->setTemporaryValue('ai_inventory/integrations/' . $type . '/' . $code . '/api_url', $apiUrl);
        }
        
        if (!empty($clientId)) {
            $this->config->setTemporaryValue('ai_inventory/integrations/' . $type . '/' . $code . '/client_id', $clientId);
        }
        
        if (!empty($clientSecret)) {
            $this->config->setTemporaryValue('ai_inventory/integrations/' . $type . '/' . $code . '/client_secret', $clientSecret);
        }
    }
} 