<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\OrderRoutingRepositoryInterface;
use AI\InventoryOptimizer\Model\ResourceModel\OrderRouting as OrderRoutingResource;
use AI\InventoryOptimizer\Model\OrderRoutingFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class OrderRoutingRepository implements OrderRoutingRepositoryInterface
{
    /**
     * @var OrderRoutingResource
     */
    private $orderRoutingResource;
    
    /**
     * @var OrderRoutingFactory
     */
    private $orderRoutingFactory;
    
    /**
     * @param OrderRoutingResource $orderRoutingResource
     * @param OrderRoutingFactory $orderRoutingFactory
     */
    public function __construct(
        OrderRoutingResource $orderRoutingResource,
        OrderRoutingFactory $orderRoutingFactory
    ) {
        $this->orderRoutingResource = $orderRoutingResource;
        $this->orderRoutingFactory = $orderRoutingFactory;
    }
    
    /**
     * Save order routing
     *
     * @param \AI\InventoryOptimizer\Model\OrderRouting $orderRouting
     * @return \AI\InventoryOptimizer\Model\OrderRouting
     * @throws CouldNotSaveException
     */
    public function save($orderRouting)
    {
        try {
            $this->orderRoutingResource->save($orderRouting);
            return $orderRouting;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save order routing: %1', $e->getMessage()));
        }
    }
    
    /**
     * Get order routing by ID
     *
     * @param int $routingId
     * @return \AI\InventoryOptimizer\Model\OrderRouting
     * @throws NoSuchEntityException
     */
    public function getById($routingId)
    {
        $orderRouting = $this->orderRoutingFactory->create();
        $this->orderRoutingResource->load($orderRouting, $routingId);
        
        if (!$orderRouting->getId()) {
            throw new NoSuchEntityException(__('Order routing with ID "%1" does not exist.', $routingId));
        }
        
        return $orderRouting;
    }
    
    /**
     * Get order routing by order ID
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Model\OrderRouting
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId)
    {
        $orderRouting = $this->orderRoutingFactory->create();
        $this->orderRoutingResource->load($orderRouting, $orderId, 'order_id');
        
        if (!$orderRouting->getId()) {
            throw new NoSuchEntityException(__('Order routing for order ID "%1" does not exist.', $orderId));
        }
        
        return $orderRouting;
    }
    
    /**
     * Delete order routing
     *
     * @param \AI\InventoryOptimizer\Model\OrderRouting $orderRouting
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($orderRouting)
    {
        try {
            $this->orderRoutingResource->delete($orderRouting);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete order routing: %1', $e->getMessage()));
        }
    }
    
    /**
     * Delete order routing by ID
     *
     * @param int $routingId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($routingId)
    {
        return $this->delete($this->getById($routingId));
    }
} 