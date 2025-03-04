<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Model\MagicMoments\OpportunityDetector;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;

class DetectOpportunities
{
    /**
     * @var OpportunityDetector
     */
    private $opportunityDetector;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param OpportunityDetector $opportunityDetector
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        OpportunityDetector $opportunityDetector,
        Config $config,
        Logger $logger
    ) {
        $this->opportunityDetector = $opportunityDetector;
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
        if (!$this->config->isModuleEnabled() || !$this->config->isMagicMomentsEnabled()) {
            return;
        }
        
        $this->logger->info('Starting scheduled opportunity detection');
        
        try {
            $this->opportunityDetector->detectAllOpportunities();
            $this->logger->info('Scheduled opportunity detection completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Error during scheduled opportunity detection: ' . $e->getMessage());
        }
    }
} 