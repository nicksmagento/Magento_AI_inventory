<?php
namespace AI\InventoryOptimizer\Api;

interface FraudCheckRepositoryInterface
{
    /**
     * Save fraud check
     *
     * @param \AI\InventoryOptimizer\Model\FraudCheck $fraudCheck
     * @return \AI\InventoryOptimizer\Model\FraudCheck
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($fraudCheck);
    
    /**
     * Get fraud check by ID
     *
     * @param int $fraudCheckId
     * @return \AI\InventoryOptimizer\Model\FraudCheck
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($fraudCheckId);
    
    /**
     * Get fraud check by order ID
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Model\FraudCheck
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId($orderId);
    
    /**
     * Delete fraud check
     *
     * @param \AI\InventoryOptimizer\Model\FraudCheck $fraudCheck
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($fraudCheck);
    
    /**
     * Delete fraud check by ID
     *
     * @param int $fraudCheckId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($fraudCheckId);
} 