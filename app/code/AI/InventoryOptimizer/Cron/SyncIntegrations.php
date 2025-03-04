<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Model\IntegrationSyncService;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;

class SyncIntegrations
{
    /**
     * @var IntegrationSyncService
     */
    private $integrationSyncService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param IntegrationSyncService $integrationSyncService
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        IntegrationSyncService $integrationSyncService,
        Config $config,
        Logger $logger
    ) {
        $this->integrationSyncService = $integrationSyncService;
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isModuleEnabled() || !$this->config->isIntegrationsEnabled()) {
            return;
        }
        
        try {
            $this->logger->info('Starting integration synchronization cron job');
            
            // Sync inventory
            $inventoryResults = $this->integrationSyncService->syncInventory();
            $this->logger->info('Inventory sync results: ' . json_encode($inventoryResults));
            
            // Sync orders
            $orderResults = $this->integrationSyncService->syncOrders();
            $this->logger->info('Order sync results: ' . json_encode($orderResults));
            
            $this->logger->info('Completed integration synchronization cron job');
        } catch (\Exception $e) {
            $this->logger->error('Error in integration synchronization cron job: ' . $e->getMessage());
        }
    }
} 