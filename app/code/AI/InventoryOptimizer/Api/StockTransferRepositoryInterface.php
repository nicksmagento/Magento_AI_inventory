<?php
namespace AI\InventoryOptimizer\Api;

interface StockTransferRepositoryInterface
{
    /**
     * Save stock transfer
     *
     * @param \AI\InventoryOptimizer\Model\StockTransfer $stockTransfer
     * @return \AI\InventoryOptimizer\Model\StockTransfer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($stockTransfer);
    
    /**
     * Get stock transfer by ID
     *
     * @param int $transferId
     * @return \AI\InventoryOptimizer\Model\StockTransfer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($transferId);
    
    /**
     * Delete stock transfer
     *
     * @param \AI\InventoryOptimizer\Model\StockTransfer $stockTransfer
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($stockTransfer);
    
    /**
     * Delete stock transfer by ID
     *
     * @param int $transferId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($transferId);
} 