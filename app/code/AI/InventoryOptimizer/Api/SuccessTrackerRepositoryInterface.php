<?php
namespace AI\InventoryOptimizer\Api;

use AI\InventoryOptimizer\Api\Data\SuccessTrackerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SuccessTrackerRepositoryInterface
{
    /**
     * Save success tracker
     *
     * @param SuccessTrackerInterface $successTracker
     * @return SuccessTrackerInterface
     */
    public function save(SuccessTrackerInterface $successTracker);
    
    /**
     * Get by id
     *
     * @param int $id
     * @return SuccessTrackerInterface
     */
    public function getById($id);
    
    /**
     * Get list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \AI\InventoryOptimizer\Api\Data\SuccessTrackerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
    
    /**
     * Delete
     *
     * @param SuccessTrackerInterface $successTracker
     * @return bool
     */
    public function delete(SuccessTrackerInterface $successTracker);
    
    /**
     * Delete by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
} 