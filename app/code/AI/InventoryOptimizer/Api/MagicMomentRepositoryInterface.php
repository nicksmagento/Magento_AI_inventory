<?php
namespace AI\InventoryOptimizer\Api;

use AI\InventoryOptimizer\Api\Data\MagicMomentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface MagicMomentRepositoryInterface
{
    /**
     * Save magic moment
     *
     * @param MagicMomentInterface $magicMoment
     * @return MagicMomentInterface
     * @throws CouldNotSaveException
     */
    public function save(MagicMomentInterface $magicMoment);

    /**
     * Get magic moment by ID
     *
     * @param int $id
     * @return MagicMomentInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete magic moment
     *
     * @param MagicMomentInterface $magicMoment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(MagicMomentInterface $magicMoment);

    /**
     * Delete magic moment by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Get magic moments list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \AI\InventoryOptimizer\Api\Data\MagicMomentSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get unread magic moments count
     *
     * @return int
     */
    public function getUnreadCount();

    /**
     * Mark magic moment as read
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function markAsRead($id);

    /**
     * Mark magic moment as actioned
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function markAsActioned($id);
} 