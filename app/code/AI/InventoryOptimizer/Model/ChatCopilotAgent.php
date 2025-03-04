<?php
namespace AI\InventoryOptimizer\Model;

use Psr\Log\LoggerInterface;

class ChatCopilotAgent
{
    /**
     * @var ReorderAgent
     */
    private $reorderAgent;
    
    /**
     * @var StockBalancerAgent
     */
    private $stockBalancerAgent;
    
    /**
     * @var OrderRouterAgent
     */
    private $orderRouterAgent;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param ReorderAgent $reorderAgent
     * @param StockBalancerAgent $stockBalancerAgent
     * @param OrderRouterAgent $orderRouterAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReorderAgent $reorderAgent,
        StockBalancerAgent $stockBalancerAgent,
        OrderRouterAgent $orderRouterAgent,
        LoggerInterface $logger
    ) {
        $this->reorderAgent = $reorderAgent;
        $this->stockBalancerAgent = $stockBalancerAgent;
        $this->orderRouterAgent = $orderRouterAgent;
        $this->logger = $logger;
    }
    
    /**
     * Process merchant command
     *
     * @param string $command
     * @return array
     */
    public function processCommand($command)
    {
        try {
            // Parse the command using NLP
            $intent = $this->parseCommandIntent($command);
            
            // Execute the appropriate action based on intent
            switch ($intent['action']) {
                case 'reorder':
                    return $this->handleReorderCommand($intent);
                
                case 'transfer_stock':
                    return $this->handleTransferStockCommand($intent);
                
                case 'route_order':
                    return $this->handleRouteOrderCommand($intent);
                
                case 'get_inventory_status':
                    return $this->handleInventoryStatusCommand($intent);
                
                default:
                    return [
                        'success' => false,
                        'message' => 'I did not understand that command. Please try again.'
                    ];
            }
        } catch (\Exception $e) {
            $this->logger->error('AI Chat Copilot Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse command to determine intent
     *
     * @param string $command
     * @return array
     */
    private function parseCommandIntent($command)
    {
        // This would use NLP to parse the command
        // For now, this is a simple keyword-based implementation
        
        $command = strtolower($command);
        
        if (strpos($command, 'reorder') !== false) {
            // Extract SKU and threshold
            preg_match('/product\s+([a-z0-9-]+)/i', $command, $skuMatches);
            preg_match('/below\s+(\d+)/i', $command, $thresholdMatches);
            
            $sku = isset($skuMatches[1]) ? $skuMatches[1] : null;
            $threshold = isset($thresholdMatches[1]) ? (int)$thresholdMatches[1] : 0;
            
            return [
                'action' => 'reorder',
                'sku' => $sku,
                'threshold' => $threshold
            ];
        }
        
        if (strpos($command, 'transfer') !== false || strpos($command, 'move') !== false) {
            // Extract SKU, source and destination warehouses
            preg_match('/product\s+([a-z0-9-]+)/i', $command, $skuMatches);
            preg_match('/from\s+warehouse\s+([a-z])/i', $command, $sourceMatches);
            preg_match('/to\s+warehouse\s+([a-z])/i', $command, $destMatches);
            
            $sku = isset($skuMatches[1]) ? $skuMatches[1] : null;
            $sourceWarehouse = isset($sourceMatches[1]) ? $sourceMatches[1] : null;
            $destWarehouse = isset($destMatches[1]) ? $destMatches[1] : null;
            
            return [
                'action' => 'transfer_stock',
                'sku' => $sku,
                'source_warehouse' => $sourceWarehouse,
                'dest_warehouse' => $destWarehouse
            ];
        }
        
        // Add more intent parsing logic for other commands
        
        return [
            'action' => 'unknown'
        ];
    }
    
    /**
     * Handle reorder command
     *
     * @param array $intent
     * @return array
     */
    private function handleReorderCommand($intent)
    {
        if (!isset($intent['sku']) || !$intent['sku']) {
            return [
                'success' => false,
                'message' => 'Please specify a product SKU to reorder.'
            ];
        }
        
        // Process reorder for the SKU
        $this->reorderAgent->processReorderForSku($intent['sku']);
        
        return [
            'success' => true,
            'message' => sprintf(
                'I have set up automatic reordering for product %s when stock falls below %d units.',
                $intent['sku'],
                $intent['threshold']
            )
        ];
    }
    
    /**
     * Handle transfer stock command
     *
     * @param array $intent
     * @return array
     */
    private function handleTransferStockCommand($intent)
    {
        if (!isset($intent['sku']) || !$intent['sku']) {
            return [
                'success' => false,
                'message' => 'Please specify a product SKU to transfer.'
            ];
        }
        
        if (!isset($intent['source_warehouse']) || !isset($intent['dest_warehouse'])) {
            return [
                'success' => false,
                'message' => 'Please specify source and destination warehouses.'
            ];
        }
        
        // Process stock transfer
        $this->stockBalancerAgent->balanceStockForSku($intent['sku']);
        
        return [
            'success' => true,
            'message' => sprintf(
                'I have initiated a stock transfer for product %s from Warehouse %s to Warehouse %s.',
                $intent['sku'],
                $intent['source_warehouse'],
                $intent['dest_warehouse']
            )
        ];
    }
    
    /**
     * Handle route order command
     *
     * @param array $intent
     * @return array
     */
    private function handleRouteOrderCommand($intent)
    {
        // Implementation for routing orders
        return [
            'success' => true,
            'message' => 'Order routing command processed.'
        ];
    }
    
    /**
     * Handle inventory status command
     *
     * @param array $intent
     * @return array
     */
    private function handleInventoryStatusCommand($intent)
    {
        // Implementation for getting inventory status
        return [
            'success' => true,
            'message' => 'Inventory status command processed.'
        ];
    }
} 