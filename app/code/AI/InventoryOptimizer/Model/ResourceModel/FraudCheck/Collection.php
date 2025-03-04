<?php
namespace AI\InventoryOptimizer\Model\ResourceModel\FraudCheck;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \AI\InventoryOptimizer\Model\FraudCheck::class,
            \AI\InventoryOptimizer\Model\ResourceModel\FraudCheck::class
        );
    }
} 