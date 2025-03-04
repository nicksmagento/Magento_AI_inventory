<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\StockTransferRepositoryInterface;
use AI\InventoryOptimizer\Model\ResourceModel\StockTransfer as StockTransferResource;
use AI\InventoryOptimizer\Model\StockTransferFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class StockTransferRepository implements StockTransferRepositoryInterface
{
    /**
     * @var StockTransferResource
     */
    private $stockTransferResource;
    
    /**
     * @var StockTransferFactory
     */
    private $stockTransferFactory;
    
    /**
     * @param StockTransferResource $stockTransferResource
     * @param StockTransferFactory $stockTransferFactory
     */
    public function __construct(
        StockTransferResource $stockTransferResource,
        StockTransferFactory $stockTransferFactory
    ) {
        $this->stockTransferResource = $stockTransferResource;
        $this->stockTransferFactory = $stockTransferFactory;
    }
    
    /**
     * Save stock transfer
     *
     * @param \AI\InventoryOptimizer\Model\StockTransfer $stockTransfer
     * @return \AI\InventoryOptimizer\Model\StockTransfer
     * @throws CouldNotSaveException
     */
    public function save($stockTransfer)
    {
        try {
            $this->stockTransferResource->save($stockTransfer);
            return $stockTransfer;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save stock transfer: %1', $e->getMessage()));
        }
    }
    
    /**
     * Get stock transfer by ID
     *
     * @param int $transferId
     * @return \AI\InventoryOptimizer\Model\StockTransfer
     * @throws NoSuchEntityException
     */
    public function getById($transferId)
    {
        $stockTransfer = $this->stockTransferFactory->create();
        $this->stockTransferResource->load($stockTransfer, $transferId);
        
        if (!$stockTransfer->getId()) {
            throw new NoSuchEntityException(__('Stock transfer with ID "%1" does not exist.', $transferId));
        }
        
        return $stockTransfer;
    }
    
    /**
     * Delete stock transfer
     *
     * @param \AI\InventoryOptimizer\Model\StockTransfer $stockTransfer
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($stockTransfer)
    {
        try {
            $this->stockTransferResource->delete($stockTransfer);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete stock transfer: %1', $e->getMessage()));
        }
    }
    
    /**
     * Delete stock transfer by ID
     *
     * @param int $transferId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($transferId)
    {
        return $this->delete($this->getById($transferId));
    }
} 