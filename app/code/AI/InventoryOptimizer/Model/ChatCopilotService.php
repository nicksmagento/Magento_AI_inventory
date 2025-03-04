<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\ChatCopilotServiceInterface;
use AI\InventoryOptimizer\Api\Data\ChatResponseInterfaceFactory;
use AI\InventoryOptimizer\Model\Service\AiService;
use AI\InventoryOptimizer\Helper\Config;
use AI\InventoryOptimizer\Model\ReorderAgent;
use AI\InventoryOptimizer\Model\StockBalancerAgent;
use AI\InventoryOptimizer\Model\OrderRouterAgent;
use Psr\Log\LoggerInterface;

class ChatCopilotService implements ChatCopilotServiceInterface
{
    /**
     * @var AiService
     */
    private $aiService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var ChatResponseInterfaceFactory
     */
    private $chatResponseFactory;
    
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
     * @param AiService $aiService
     * @param Config $config
     * @param ChatResponseInterfaceFactory $chatResponseFactory
     * @param ReorderAgent $reorderAgent
     * @param StockBalancerAgent $stockBalancerAgent
     * @param OrderRouterAgent $orderRouterAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        AiService $aiService,
        Config $config,
        ChatResponseInterfaceFactory $chatResponseFactory,
        ReorderAgent $reorderAgent,
        StockBalancerAgent $stockBalancerAgent,
        OrderRouterAgent $orderRouterAgent,
        LoggerInterface $logger
    ) {
        $this->aiService = $aiService;
        $this->config = $config;
        $this->chatResponseFactory = $chatResponseFactory;
        $this->reorderAgent = $reorderAgent;
        $this->stockBalancerAgent = $stockBalancerAgent;
        $this->orderRouterAgent = $orderRouterAgent;
        $this->logger = $logger;
    }
    
    /**
     * Process a chat command
     *
     * @param string $command
     * @return \AI\InventoryOptimizer\Api\Data\ChatResponseInterface
     */
    public function processCommand($command)
    {
        try {
            if (!$this->config->isEnabled()) {
                throw new \Exception('AI Inventory Optimizer is disabled in configuration');
            }
            
            // Process the command using AI service
            $nlpResult = $this->aiService->processNaturalLanguageCommand($command);
            
            // Execute the appropriate action based on intent
            $success = true;
            $message = $nlpResult['response'];
            
            switch ($nlpResult['intent']) {
                case 'reorder':
                    if (isset($nlpResult['parameters']['sku'])) {
                        $this->reorderAgent->processReorderForSku($nlpResult['parameters']['sku']);
                    }
                    break;
                
                case 'transfer_stock':
                    if (isset($nlpResult['parameters']['sku'])) {
                        $this->stockBalancerAgent->balanceStockForSku($nlpResult['parameters']['sku']);
                    }
                    break;
                
                case 'inventory_status':
                    // This is handled by the AI service response
                    break;
                
                case 'unknown':
                    $success = false;
                    break;
            }
            
            // Create and return chat response
            $response = $this->chatResponseFactory->create();
            $response->setSuccess($success);
            $response->setMessage($message);
            $response->setIntent($nlpResult['intent']);
            $response->setParameters($nlpResult['parameters']);
            
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Chat Copilot Service Error: ' . $e->getMessage());
            
            $response = $this->chatResponseFactory->create();
            $response->setSuccess(false);
            $response->setMessage('An error occurred: ' . $e->getMessage());
            $response->setIntent('error');
            $response->setParameters([]);
            
            return $response;
        }
    }
} 