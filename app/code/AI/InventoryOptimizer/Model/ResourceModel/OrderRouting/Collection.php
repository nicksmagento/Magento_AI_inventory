<?php
namespace AI\InventoryOptimizer\Model\ResourceModel\OrderRouting;

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
            \AI\InventoryOptimizer\Model\OrderRouting::class,
            \AI\InventoryOptimizer\Model\ResourceModel\OrderRouting::class
        );
    }
} 