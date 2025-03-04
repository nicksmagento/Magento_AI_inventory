<?php
namespace AI\InventoryOptimizer\Api;

interface ChatCopilotServiceInterface
{
    /**
     * Process a chat command
     *
     * @param string $command
     * @return \AI\InventoryOptimizer\Api\Data\ChatResponseInterface
     */
    public function processCommand($command);
} 