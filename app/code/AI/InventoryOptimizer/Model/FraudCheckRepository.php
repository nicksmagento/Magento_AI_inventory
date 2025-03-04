<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\FraudCheckRepositoryInterface;
use AI\InventoryOptimizer\Model\ResourceModel\FraudCheck as FraudCheckResource;
use AI\InventoryOptimizer\Model\FraudCheckFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class FraudCheckRepository implements FraudCheckRepositoryInterface
{
    /**
     * @var FraudCheckResource
     */
    private $fraudCheckResource;
    
    /**
     * @var FraudCheckFactory
     */
    private $fraudCheckFactory;
    
    /**
     * @param FraudCheckResource $fraudCheckResource
     * @param FraudCheckFactory $fraudCheckFactory
     */
    public function __construct(
        FraudCheckResource $fraudCheckResource,
        FraudCheckFactory $fraudCheckFactory
    ) {
        $this->fraudCheckResource = $fraudCheckResource;
        $this->fraudCheckFactory = $fraudCheckFactory;
    }
    
    /**
     * Save fraud check
     *
     * @param \AI\InventoryOptimizer\Model\FraudCheck $fraudCheck
     * @return \AI\InventoryOptimizer\Model\FraudCheck
     * @throws CouldNotSaveException
     */
    public function save($fraudCheck)
    {
        try {
            $this->fraudCheckResource->save($fraudCheck);
            return $fraudCheck;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save fraud check: %1', $e->getMessage()));
        }
    }
    
    /**
     * Get fraud check by ID
     *
     * @param int $fraudCheckId
     * @return \AI\InventoryOptimizer\Model\FraudCheck
     * @throws NoSuchEntityException
     */
    public function getById($fraudCheckId)
    {
        $fraudCheck = $this->fraudCheckFactory->create();
        $this->fraudCheckResource->load($fraudCheck, $fraudCheckId);
        
        if (!$fraudCheck->getId()) {
            throw new NoSuchEntityException(__('Fraud check with ID "%1" does not exist.', $fraudCheckId));
        }
        
        return $fraudCheck;
    }
    
    /**
     * Get fraud check by order ID
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Model\FraudCheck
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId)
    {
        $fraudCheck = $this->fraudCheckFactory->create();
        $this->fraudCheckResource->load($fraudCheck, $orderId, 'order_id');
        
        if (!$fraudCheck->getId()) {
            throw new NoSuchEntityException(__('Fraud check for order ID "%1" does not exist.', $orderId));
        }
        
        return $fraudCheck;
    }
    
    /**
     * Delete fraud check
     *
     * @param \AI\InventoryOptimizer\Model\FraudCheck $fraudCheck
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($fraudCheck)
    {
        try {
            $this->fraudCheckResource->delete($fraudCheck);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete fraud check: %1', $e->getMessage()));
        }
    }
    
    /**
     * Delete fraud check by ID
     *
     * @param int $fraudCheckId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($fraudCheckId)
    {
        return $this->delete($this->getById($fraudCheckId));
    }
} 