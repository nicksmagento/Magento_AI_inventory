<?php
namespace AI\InventoryOptimizer\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use AI\InventoryOptimizer\Model\IntegrationSyncService;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;

class SyncAll extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var IntegrationSyncService
     */
    protected $integrationSyncService;
    
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
     * @param IntegrationSyncService $integrationSyncService
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        IntegrationSyncService $integrationSyncService,
        Config $config,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->integrationSyncService = $integrationSyncService;
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * Sync all integrations
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            if (!$this->config->isModuleEnabled() || !$this->config->isIntegrationsEnabled()) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Integrations are not enabled')
                ]);
            }
            
            // Sync inventory
            $inventoryResults = $this->integrationSyncService->syncInventory();
            
            // Sync orders
            $orderResults = $this->integrationSyncService->syncOrders();
            
            // Count successful syncs
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($inventoryResults as $code => $syncResult) {
                if (isset($syncResult['success']) && $syncResult['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            return $result->setData([
                'success' => true,
                'message' => __('Synchronized with %1 integrations successfully. %2 integrations had errors.', $successCount, $errorCount),
                'inventory_results' => $inventoryResults,
                'order_results' => $orderResults
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Integration sync all error: ' . $e->getMessage());
            
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 