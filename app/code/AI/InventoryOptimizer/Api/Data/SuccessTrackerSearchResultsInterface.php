<?php
namespace AI\InventoryOptimizer\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface SuccessTrackerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get success tracker list
     *
     * @return \AI\InventoryOptimizer\Api\Data\SuccessTrackerInterface[]
     */
    public function getItems();
    
    /**
     * Set success tracker list
     *
     * @param \AI\InventoryOptimizer\Api\Data\SuccessTrackerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
} 