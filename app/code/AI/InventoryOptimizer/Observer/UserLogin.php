<?php
namespace AI\InventoryOptimizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Model\Onboarding\InstantValueGenerator;
use AI\InventoryOptimizer\Logger\Logger;

class UserLogin implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var InstantValueGenerator
     */
    private $instantValueGenerator;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param Config $config
     * @param InstantValueGenerator $instantValueGenerator
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        InstantValueGenerator $instantValueGenerator,
        Logger $logger
    ) {
        $this->config = $config;
        $this->instantValueGenerator = $instantValueGenerator;
        $this->logger = $logger;
    }
    
    /**
     * Observer execution
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isModuleEnabled() || !$this->config->isOnboardingEnabled()) {
            return;
        }
        
        try {
            $user = $observer->getEvent()->getUser();
            
            // Check if this is a new user or needs onboarding
            // Logic to determine if user needs onboarding would go here
            
            // Generate instant insights for the user
            $this->instantValueGenerator->generateInstantInsights();
            
            $this->logger->info('Generated instant insights for user login: ' . $user->getId());
        } catch (\Exception $e) {
            $this->logger->error('Error generating insights on user login: ' . $e->getMessage());
        }
    }
} 