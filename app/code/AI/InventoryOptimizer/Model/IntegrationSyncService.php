<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Model\IntegrationRegistry;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\Event\ManagerInterface as EventManager;

class IntegrationSyncService
{
    /**
     * @var IntegrationRegistry
     */
    private $integrationRegistry;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var EventManager
     */
    private $eventManager;
    
    /**
     * @param IntegrationRegistry $integrationRegistry
     * @param Logger $logger
     * @param EventManager $eventManager
     */
    public function __construct(
        IntegrationRegistry $integrationRegistry,
        Logger $logger,
        EventManager $eventManager
    ) {
        $this->integrationRegistry = $integrationRegistry;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }
    
    /**
     * Synchronize inventory with all enabled integrations
     *
     * @param array $filters
     * @return array
     */
    public function syncInventory(array $filters = [])
    {
        $results = [];
        $enabledIntegrations = $this->integrationRegistry->getEnabledIntegrations();
        
        foreach ($enabledIntegrations as $code => $integration) {
            try {
                $this->logger->info("Starting inventory sync with {$integration->getName()}");
                
                // Import inventory from external system
                $externalInventory = $integration->importInventory($filters);
                
                // Process imported inventory
                if (!empty($externalInventory)) {
                    // Dispatch event for other modules to process
                    $this->eventManager->dispatch(
                        'ai_inventory_integration_import',
                        [
                            'integration' => $code,
                            'inventory' => $externalInventory
                        ]
                    );
                    
                    $results[$code] = [
                        'success' => true,
                        'imported' => count($externalInventory),
                        'message' => "Successfully imported " . count($externalInventory) . " items from {$integration->getName()}"
                    ];
                } else {
                    $results[$code] = [
                        'success' => true,
                        'imported' => 0,
                        'message' => "No inventory items to import from {$integration->getName()}"
                    ];
                }
                
                $this->logger->info("Completed inventory sync with {$integration->getName()}: " . $results[$code]['message']);
            } catch (\Exception $e) {
                $this->logger->error("Error syncing inventory with {$integration->getName()}: " . $e->getMessage());
                
                $results[$code] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Synchronize orders with all enabled integrations
     *
     * @param array $filters
     * @return array
     */
    public function syncOrders(array $filters = [])
    {
        $results = [];
        $enabledIntegrations = $this->integrationRegistry->getEnabledIntegrations();
        
        foreach ($enabledIntegrations as $code => $integration) {
            try {
                $this->logger->info("Starting order sync with {$integration->getName()}");
                
                // Import orders from external system
                $externalOrders = $integration->importOrders($filters);
                
                // Process imported orders
                if (!empty($externalOrders)) {
                    // Dispatch event for other modules to process
                    $this->eventManager->dispatch(
                        'ai_inventory_integration_import_orders',
                        [
                            'integration' => $code,
                            'orders' => $externalOrders
                        ]
                    );
                    
                    $results[$code] = [
                        'success' => true,
                        'imported' => count($externalOrders),
                        'message' => "Successfully imported " . count($externalOrders) . " orders from {$integration->getName()}"
                    ];
                } else {
                    $results[$code] = [
                        'success' => true,
                        'imported' => 0,
                        'message' => "No orders to import from {$integration->getName()}"
                    ];
                }
                
                $this->logger->info("Completed order sync with {$integration->getName()}: " . $results[$code]['message']);
            } catch (\Exception $e) {
                $this->logger->error("Error syncing orders with {$integration->getName()}: " . $e->getMessage());
                
                $results[$code] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Export inventory to external systems
     *
     * @param array $data
     * @param string|null $integrationCode
     * @return array
     */
    public function exportInventory(array $data, $integrationCode = null)
    {
        $results = [];
        
        if ($integrationCode) {
            // Export to specific integration
            $integration = $this->integrationRegistry->getIntegration($integrationCode);
            if (!$integration || !$integration->isEnabled()) {
                return [
                    'success' => false,
                    'message' => "Integration {$integrationCode} is not enabled or does not exist"
                ];
            }
            
            try {
                $this->logger->info("Exporting inventory to {$integration->getName()}");
                $success = $integration->exportInventory($data);
                
                $results[$integrationCode] = [
                    'success' => $success,
                    'exported' => count($data),
                    'message' => $success 
                        ? "Successfully exported " . count($data) . " items to {$integration->getName()}"
                        : "Failed to export inventory to {$integration->getName()}"
                ];
                
                $this->logger->info("Completed inventory export to {$integration->getName()}: " . $results[$integrationCode]['message']);
            } catch (\Exception $e) {
                $this->logger->error("Error exporting inventory to {$integration->getName()}: " . $e->getMessage());
                
                $results[$integrationCode] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        } else {
            // Export to all enabled integrations
            $enabledIntegrations = $this->integrationRegistry->getEnabledIntegrations();
            
            foreach ($enabledIntegrations as $code => $integration) {
                try {
                    $this->logger->info("Exporting inventory to {$integration->getName()}");
                    $success = $integration->exportInventory($data);
                    
                    $results[$code] = [
                        'success' => $success,
                        'exported' => count($data),
                        'message' => $success 
                            ? "Successfully exported " . count($data) . " items to {$integration->getName()}"
                            : "Failed to export inventory to {$integration->getName()}"
                    ];
                    
                    $this->logger->info("Completed inventory export to {$integration->getName()}: " . $results[$code]['message']);
                } catch (\Exception $e) {
                    $this->logger->error("Error exporting inventory to {$integration->getName()}: " . $e->getMessage());
                    
                    $results[$code] = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Export orders to external systems
     *
     * @param array $data
     * @param string|null $integrationCode
     * @return array
     */
    public function exportOrders(array $data, $integrationCode = null)
    {
        $results = [];
        
        if ($integrationCode) {
            // Export to specific integration
            $integration = $this->integrationRegistry->getIntegration($integrationCode);
            if (!$integration || !$integration->isEnabled()) {
                return [
                    'success' => false,
                    'message' => "Integration {$integrationCode} is not enabled or does not exist"
                ];
            }
            
            try {
                $this->logger->info("Exporting orders to {$integration->getName()}");
                $success = $integration->exportOrders($data);
                
                $results[$integrationCode] = [
                    'success' => $success,
                    'exported' => count($data),
                    'message' => $success 
                        ? "Successfully exported " . count($data) . " orders to {$integration->getName()}"
                        : "Failed to export orders to {$integration->getName()}"
                ];
                
                $this->logger->info("Completed order export to {$integration->getName()}: " . $results[$integrationCode]['message']);
            } catch (\Exception $e) {
                $this->logger->error("Error exporting orders to {$integration->getName()}: " . $e->getMessage());
                
                $results[$integrationCode] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        } else {
            // Export to all enabled integrations
            $enabledIntegrations = $this->integrationRegistry->getEnabledIntegrations();
            
            foreach ($enabledIntegrations as $code => $integration) {
                try {
                    $this->logger->info("Exporting orders to {$integration->getName()}");
                    $success = $integration->exportOrders($data);
                    
                    $results[$code] = [
                        'success' => $success,
                        'exported' => count($data),
                        'message' => $success 
                            ? "Successfully exported " . count($data) . " orders to {$integration->getName()}"
                            : "Failed to export orders to {$integration->getName()}"
                    ];
                    
                    $this->logger->info("Completed order export to {$integration->getName()}: " . $results[$code]['message']);
                } catch (\Exception $e) {
                    $this->logger->error("Error exporting orders to {$integration->getName()}: " . $e->getMessage());
                    
                    $results[$code] = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }
        
        return $results;
    }
} 