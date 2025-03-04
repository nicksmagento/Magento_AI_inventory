<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Model\MultichannelSyncAgent;
use Psr\Log\LoggerInterface;

class MultichannelSync
{
    /**
     * @var MultichannelSyncAgent
     */
    private $multichannelSyncAgent;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param MultichannelSyncAgent $multichannelSyncAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        MultichannelSyncAgent $multichannelSyncAgent,
        LoggerInterface $logger
    ) {
        $this->multichannelSyncAgent = $multichannelSyncAgent;
        $this->logger = $logger;
    }
    
    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->logger->info('AI Multichannel Sync: Starting inventory sync across channels');
            
            // Sync inventory across all channels
            $result = $this->multichannelSyncAgent->syncAllChannels();
            
            $this->logger->info(sprintf(
                'AI Multichannel Sync: Completed sync with %d channels, %d products updated',
                $result['channels_synced'],
                $result['products_updated']
            ));
        } catch (\Exception $e) {
            $this->logger->error('AI Multichannel Sync Cron Error: ' . $e->getMessage());
        }
    }
} 