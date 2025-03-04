<?php
namespace AI\InventoryOptimizer\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use AI\InventoryOptimizer\Model\IntegrationRegistry;
use AI\InventoryOptimizer\Model\IntegrationSyncService;
use AI\InventoryOptimizer\Logger\Logger;

class Sync extends Action
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
     * @var IntegrationSyncService
     */
    protected $integrationSyncService;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param IntegrationRegistry $integrationRegistry
     * @param IntegrationSyncService $integrationSyncService
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        IntegrationRegistry $integrationRegistry,
        IntegrationSyncService $integrationSyncService,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->integrationRegistry = $integrationRegistry;
        $this->integrationSyncService = $integrationSyncService;
        $this->logger = $logger;
    }
    
    /**
     * Sync integration
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $code = $this->getRequest()->getParam('code');
            
            if (empty($code)) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Missing integration code')
                ]);
            }
            
            $integration = $this->integrationRegistry->getIntegration($code);
            
            if (!$integration) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Integration "%1" not found', $code)
                ]);
            }
            
            if (!$integration->isEnabled()) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Integration "%1" is not enabled', $integration->getName())
                ]);
            }
            
            // Sync inventory
            $inventoryResults = $this->integrationSyncService->syncInventory(['integration' => $code]);
            
            // Sync orders
            $orderResults = $this->integrationSyncService->syncOrders(['integration' => $code]);
            
            return $result->setData([
                'success' => true,
                'message' => __('Successfully synchronized with %1', $integration->getName()),
                'inventory_results' => $inventoryResults[$code] ?? [],
                'order_results' => $orderResults[$code] ?? []
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Integration sync error: ' . $e->getMessage());
            
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 