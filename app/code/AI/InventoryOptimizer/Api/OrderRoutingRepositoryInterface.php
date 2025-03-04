<?php
namespace AI\InventoryOptimizer\Api;

interface OrderRoutingRepositoryInterface
{
    /**
     * Save order routing
     *
     * @param \AI\InventoryOptimizer\Model\OrderRouting $orderRouting
     * @return \AI\InventoryOptimizer\Model\OrderRouting
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($orderRouting);
    
    /**
     * Get order routing by ID
     *
     * @param int $routingId
     * @return \AI\InventoryOptimizer\Model\OrderRouting
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($routingId);
    
    /**
     * Get order routing by order ID
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Model\OrderRouting
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId($orderId);
    
    /**
     * Delete order routing
     *
     * @param \AI\InventoryOptimizer\Model\OrderRouting $orderRouting
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($orderRouting);
    
    /**
     * Delete order routing by ID
     *
     * @param int $routingId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($routingId);
} 