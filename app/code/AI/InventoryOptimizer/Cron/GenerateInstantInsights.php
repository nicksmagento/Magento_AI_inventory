<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Model\Onboarding\InstantValueGenerator;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;

class GenerateInstantInsights
{
    /**
     * @var InstantValueGenerator
     */
    private $instantValueGenerator;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param InstantValueGenerator $instantValueGenerator
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        InstantValueGenerator $instantValueGenerator,
        Config $config,
        Logger $logger
    ) {
        $this->instantValueGenerator = $instantValueGenerator;
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
        if (!$this->config->isModuleEnabled() || !$this->config->isOnboardingEnabled() || !$this->config->isInstantValueGenerationEnabled()) {
            return;
        }
        
        $this->logger->info('Starting scheduled instant insights generation');
        
        try {
            $this->instantValueGenerator->generateInstantInsights();
            $this->logger->info('Scheduled instant insights generation completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Error during scheduled instant insights generation: ' . $e->getMessage());
        }
    }
} 