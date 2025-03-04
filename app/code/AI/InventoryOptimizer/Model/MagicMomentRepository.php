<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\Data\MagicMomentInterface;
use AI\InventoryOptimizer\Api\Data\MagicMomentSearchResultsInterfaceFactory;
use AI\InventoryOptimizer\Api\MagicMomentRepositoryInterface;
use AI\InventoryOptimizer\Model\ResourceModel\MagicMoment as MagicMomentResource;
use AI\InventoryOptimizer\Model\ResourceModel\MagicMoment\CollectionFactory as MagicMomentCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class MagicMomentRepository implements MagicMomentRepositoryInterface
{
    /**
     * @var MagicMomentResource
     */
    private $resource;

    /**
     * @var MagicMomentFactory
     */
    private $magicMomentFactory;

    /**
     * @var MagicMomentCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var MagicMomentSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param MagicMomentResource $resource
     * @param MagicMomentFactory $magicMomentFactory
     * @param MagicMomentCollectionFactory $collectionFactory
     * @param MagicMomentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        MagicMomentResource $resource,
        MagicMomentFactory $magicMomentFactory,
        MagicMomentCollectionFactory $collectionFactory,
        MagicMomentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->magicMomentFactory = $magicMomentFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save magic moment
     *
     * @param MagicMomentInterface $magicMoment
     * @return MagicMomentInterface
     * @throws CouldNotSaveException
     */
    public function save(MagicMomentInterface $magicMoment)
    {
        try {
            $this->resource->save($magicMoment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $magicMoment;
    }

    /**
     * Get magic moment by ID
     *
     * @param int $id
     * @return MagicMomentInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $magicMoment = $this->magicMomentFactory->create();
        $this->resource->load($magicMoment, $id);
        if (!$magicMoment->getId()) {
            throw new NoSuchEntityException(__('Magic moment with id "%1" does not exist.', $id));
        }
        return $magicMoment;
    }

    /**
     * Delete magic moment
     *
     * @param MagicMomentInterface $magicMoment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(MagicMomentInterface $magicMoment)
    {
        try {
            $this->resource->delete($magicMoment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete magic moment by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Get magic moments list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \AI\InventoryOptimizer\Api\Data\MagicMomentSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }

    /**
     * Get unread magic moments count
     *
     * @return int
     */
    public function getUnreadCount()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_read', ['eq' => 0]);
        return $collection->getSize();
    }

    /**
     * Mark magic moment as read
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function markAsRead($id)
    {
        $magicMoment = $this->getById($id);
        $magicMoment->setIsRead(true);
        $this->save($magicMoment);
        return true;
    }

    /**
     * Mark magic moment as actioned
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function markAsActioned($id)
    {
        $magicMoment = $this->getById($id);
        $magicMoment->setIsActioned(true);
        $this->save($magicMoment);
        return true;
    }
} 